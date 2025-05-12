<?php

namespace Echo\Framework\Http;

use App\Models\User;
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
            http_response_code(404);
            exit;
        }

        // Record the session
        db()->execute("INSERT INTO sessions (uri, ip) 
            VALUES (?,?)", [
            $request->getUri(),
            ip2long($request->getClientIp())
        ]);

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
        $error = false;

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
            http_response_code(500);

            if (in_array("api", $middleware)) {
                $error = $ex->getMessage();
            } else {
                throw $ex;
            }
        } catch (Error $err) {
            // Handle error
            http_response_code(400);

            if (in_array("api", $middleware)) {
                $error = $err->getMessage();
            } else {
                throw $err;
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
            if ($error && config("app.debug")) {
                $api_response["error"] = $error;
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
