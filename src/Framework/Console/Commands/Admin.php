<?php

namespace Echo\Framework\Console\Commands;

/**
 * Admin commands
 */
class Admin extends \ConsoleKit\Command
{
    /**
    * Create new admin user
    */
    public function executeNew(array $args, array $options = []): void
    {
        if (empty($args) || count($args) < 2) {
            $this->writeerr("You must provide email and password for admin user" . PHP_EOL);
            exit;
        }

        $email = $args[0];
        $password = $args[1];

        // Check for admin user
        $user = db()->fetch("SELECT * FROM users WHERE email = ?", [$email]);
        
        if ($user) {
            $this->writeerr("This admin user already exists" . PHP_EOL);
            exit;
        }

        $hashed = password_hash($password, PASSWORD_ARGON2I);

        db()->execute("INSERT INTO users SET first_name='Administrator', 
            surname = '', role='admin', email=?, password=?", [
            $email,
            $hashed
        ]);

        $this->writeln("✓ successfully created admin user: $email");
    }
}
