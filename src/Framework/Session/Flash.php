<?php

namespace Echo\Framework\Session;

class Flash
{
    /**
        * Add a flash message to the messages array
        * @param string $type (warning,danger,success,info,etc)
        * @param string $message
        */
    public static function add(string $type, string $message): void
    {
        $flash = session()->get("flash") ?? [];
        $flash[strtolower($type)][md5($message)][] = $message;
        session()->set("flash", $flash);
    }

    /**
     * Get flash messages array
     * @return array
     */
    public static function get(): array
    {
        $flash = session()->get("flash") ?? [];
        session()->set("flash", []);
        return $flash;
    }

    public static function destroy(): void
    {
        $flash = [];
        session()->set("flash", $flash);
    }
}
