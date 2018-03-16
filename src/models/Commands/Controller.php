<?php

namespace Phroses\Commands;

use \Phroses\Exceptions\ExitException;
use function \Phroses\println;

class Controller {
    private $commands = [];

    public function addCommand(Command $command) {
        $this->commands[$command->name] = $command;
    }

    public function addCommands(array $commands) {
        foreach($commands as $command) {
            $this->addCommand($command);
        }
    }

    public function select(string $name): ?Command {
        return $this->commands[$name] ?? null;
    }

    public function execute(string $name, array $args) {
        $args = (new ArgumentParser($args))->parse();

        if(!$command = $this->select($name)) {
            println("Command '$name' not found");
            throw new ExitException(1);
        }

        $command->flags = $args["flags"];
        $command->execute(...$args["args"]);
    }
}