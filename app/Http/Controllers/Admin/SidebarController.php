<?php

namespace App\Http\Controllers\Admin;

use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Group;
use Echo\Framework\Routing\Route\Get;

#[Group(path_prefix: "/admin", middleware: ["auth"])]
class SidebarController extends Controller
{
    #[Get("/sidebar", "admin.sidebar.load")]
    public function load(): string
    {
        $links = [
            [
                "url" => "/admin/dashboard",
                "icon" => "rocket",
                "title" => "Dashboard",
            ],
            [
                "url" => "/admin/sessions",
                "icon" => "person-bounding-box",
                "title" => "Sessions",
            ],
            [
                "url" => "/admin/users",
                "icon" => "people",
                "title" => "Users",
            ],
            [
                "url" => "/sign-out",
                "icon" => "door-closed",
                "title" => "Sign Out",
                "normal" => true,
            ],
        ];
        return $this->render("admin/sidebar.html.twig", [
            "hide" => $this->getState(),
            "links" => $links
        ]);
    } 

    #[Get("/sidebar/toggle", "admin.sidebar.toggle")]
    public function toggle(): string
    {
        $state = $this->getState();
        session()->set("sidebar_state", !$state);
        return $this->load();
    } 

    private function getState()
    {
        $state = session()->get("sidebar_state");
        if (is_null($state)) {
            session()->set("sidebar_state", true);
            $state = true;
        }
        return $state;
    }
}
