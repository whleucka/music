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
        // Non-admin users must be granted permission
        if (user()->role === 'admin') {
            $modules = db()->fetchAll("SELECT *, CONCAT('/admin/', link) as url
                FROM modules 
                ORDER BY item_order");
        } else {
            $modules = db()->fetchAll("SELECT *, CONCAT('/admin/', link) as url
                FROM modules 
                WHERE EXISTS (SELECT * 
                    FROM user_permissions 
                    WHERE user_id = ? AND module_id = modules.id)
                ORDER BY item_order", [user()->id]);
        }
        return $this->render("admin/sidebar.html.twig", [
            "hide" => $this->getState(),
            "modules" => $modules
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
