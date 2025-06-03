<?php

namespace Echo\Framework\Http;

use App\Models\User;
use chillerlan\QRCode\QRCode;
use Echo\Framework\Http\Response as HttpResponse;
use Echo\Interface\Http\Kernel as HttpKernel;
use Echo\Interface\Http\Request;
use Echo\Interface\Http\Response;
use Error;
use Exception;

class Kernel implements HttpKernel
{
    // Middleware layers
    protected array $middleware_layers = [];

    public function handle(Request $request): void
    {
        // Dispatch the route
        $route = router()->dispatch($request->getUri(), $request->getMethod());

        // If there is no route, then 404
        if (is_null($route)) {
            $content = twig()->render("error/404.html.twig");
            $response = new HttpResponse($content, 404);
            $response->send();
            exit;
        }

        // Set the current route in the request
        $request->setAttribute("route", $route);

        // Get controller payload
        $middleware = new Middleware();
        $response = $middleware->layer($this->middleware_layers)
            ->handle($request, fn () => $this->response($route, $request));

        $response->send();
        exit;
    }

    private function response(array $route, Request $request): Response
    {
        // Resolve the controller
        $controller_class = $route['controller'];
        $method = $route['method'];
        $params = $route['params'];
        $middleware = $route['middleware'];
        $api_error = false;
        $request_id = $request->getAttribute("request_id");

        try {
            // Using the container will allow for DI
            // in the controller constructor
            $controller = container()->get($controller_class);

            // Set the controller request
            $controller->setRequest($request);

            // Set the application user
            $uuid = session()->get("user_uuid");
            if ($uuid) {
                $user = User::where("uuid", $uuid)->get();
                $controller->setUser($user);
            }

            // Set the content from the controller endpoint
            $content = $controller->$method(...$params);
        } catch (Exception $ex) {
            // Handle exception
            if (in_array("api", $middleware)) {
                $api_error = $ex->getMessage();
            } else {
                $content = twig()->render("error/blue-screen.html.twig", [
                    "message" => "An uncaught exception occurred.",
                    "debug" => config("app.debug"),
                    "request_id" => $request_id,
                    "e" => $ex,
                    "qr" => (new QRCode)->render($request_id),
                    "is_logged" => session()->get("user_uuid"),
                ]);
                $response = new HttpResponse($content, 500);
                return $response;
            }
        } catch (Error $err) {
            // Handle error
            if (in_array("api", $middleware)) {
                $api_error = $err->getMessage();
            } else {
                $content = twig()->render("error/blue-screen.html.twig", [
                    "message" => "A fatal error occurred.",
                    "debug" => config("app.debug"),
                    "request_id" => $request_id,
                    "e" => $err,
                    "qr" => (new QRCode)->render($request_id),
                    "is_logged" => session()->get("user_uuid"),
                ]);
                $response = new HttpResponse($content, 500);
                return $response;
            }
        }

        // Create response (api or web)
        if (in_array("api", $middleware)) {
            $code = http_response_code();
            $api_response = [
                "id" => $request->getAttribute("request_id"),
                "success" => $code === 200,
                "status" => $code,
                "data" => $content ?? null,
                "ts" => date(DATE_ATOM),
            ];
            // Only show api errors when debug is enabled
            if ($api_error && config("app.debug")) {
                $api_response["error"] = $api_error;
            }
            // API response
            $response = new JsonResponse($api_response);
        } else {
            // Web response
            $response = new HttpResponse($content);
        }

        // Set the headers
        foreach ($controller->getHeaders() as $key => $value) {
            $response->setHeader($key, $value);
        }

        return $response;
    }
}
