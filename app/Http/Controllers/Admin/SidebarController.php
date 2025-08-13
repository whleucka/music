<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Group;
use Echo\Framework\Routing\Route\Get;

#[Group(path_prefix: "/admin", middleware: ["auth"])]
class SidebarController extends Controller
{
    private function getLinks(array $nodes = [], array $modules = [], User $user)
    {
        if (empty($nodes)) {
            $nodes = db()->fetchAll("SELECT * 
                FROM modules 
                WHERE parent_id IS NULL AND enabled = 1
                ORDER BY item_order");
        }
        foreach ($nodes as $node) {
            if ($user->role === 'admin') {
            $children = db()->fetchAll("SELECT *, CONCAT('/admin/', link) as url
                FROM modules 
                WHERE parent_id = ? AND enabled = 1
                ORDER BY item_order", [$node['id']]);
            } else {
                $children = db()->fetchAll("SELECT *, CONCAT('/admin/', link) as url
                    FROM modules 
                    WHERE parent_id = ? AND 
                    enabled = 1 AND
                    EXISTS (SELECT * 
                        FROM user_permissions 
                        WHERE user_id = ? AND module_id = modules.id)
                    ORDER BY item_order
                    ", [
                    $node['id'],
                    $user->id
                ]);
            }
            if ($children) {
                $node['children'] = $this->getLinks($children, [], $user);
            }
            $modules[] = $node;
        } 
        return $modules;
    }

    #[Get("/sidebar", "admin.sidebar.load")]
    public function load(): string
    {
        $links = $this->getLinks([], [], user());
        // Non-admin users must be granted permission
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
