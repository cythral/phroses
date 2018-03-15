<?php

namespace Phroses\Commands;

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

        $command = $this->select($name);
        $command->flags = $args["flags"];
        $command->execute(...$args["args"]);
    }
}