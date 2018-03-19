<?php 

use \Phroses\Installer;
use \Phroses\Exceptions\InstallerException;
use \reqc\Output;
use \phyrex\Template;
use \Phroses\JsonServer;
use \Phroses\Switches\MethodSwitch;
use function \Phroses\handleMethod;

use const \Phroses\{ DEPS, ROOT, SRC, SCHEMAVER, INCLUDES, INPHAR, CONF_ROOT };
use const \reqc\{ MIME_TYPES };

if(!is_writable(CONF_ROOT)) {
    (new Output)->setContentType(MIME_TYPES["TXT"]);
    echo "No write access to ".CONF_ROOT.". Please fix directory permissions";
    exit(1);
}

(new MethodSwitch)

->case("post", function($out) {
    foreach(["host", "database", "username", "password"] as $req) {
        $out->error("missing_value", !isset($_POST[$req]));
    }
    
    try {
        $installer = new Installer;
        $installer->setupDatabase($_POST["host"], $_POST["database"], $_POST["username"], $_POST["password"], DEPS["MYSQL"]);
        $installer->installSchema(SRC."/schema/install.sql", SCHEMAVER);
        $installer->setupConfFile(SRC."/phroses.conf", CONF_ROOT."/phroses.conf", [
            "mode" => (INPHAR) ? "production" : "development",
            "pepper" => bin2hex(random_bytes(10))
        ]);

        $out->success();

    } catch(InstallerException $e) {
        $extra = [];
        if($e->getMessage() == "version") $extra["minver"] = DEPS["MYSQL"];

        $out->error($e->getMessage(), true, 400, $extra);
    }
}, [], JsonServer::class)

->case("get", function() {
    $install = new Template(INCLUDES["TPL"]."/installer.tpl");
    $install->styles = file_get_contents(SRC."/views/assets/css/phroses.css");
    $install->script = file_get_contents(SRC."/views/assets/js/phroses.min.js");
    echo $install;
});


