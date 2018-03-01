<?php 

use \Phroses\Installer;
use \Phroses\Exceptions\InstallerException;
use \reqc\Output;
use \phyrex\Template;
use function \Phroses\handleMethod;

use const \Phroses\{ DEPS, ROOT, SRC, SCHEMAVER, INCLUDES, INPHAR };
use const \reqc\{ MIME_TYPES };

$out = new Output();
$out->setCode(404);

if(!is_writable(ROOT)) {
    $out->setContentType(MIME_TYPES["TXT"]);
    echo "No write access to ".ROOT.". Please fix directory permissions";
    exit(1);
}

handleMethod("post", function($out) {
    try {

        $installer = new Installer;
        $installer->setupDatabase($_POST["host"], $_POST["database"], $_POST["username"], $_POST["password"], DEPS["MYSQL"]);
        $installer->installSchema(SRC."/schema/install.sql", SCHEMAVER);
        $installer->setupConfFile(SRC."/phroses.conf", ROOT."/phroses.conf", [
            "mode" => (INPHAR) ? "production" : "development",
            "pepper" => bin2hex(openssl_random_pseudo_bytes(10))
        ]);

        $out->send(["type" => "success"], 200);

    } catch(InstallerException $e) {
        $output = [ "type" => "error", "error" => $e->getMessage() ];
        if($e->getMessage() == "version") $output["minver"] = DEPS["MYSQL"];

        $out->send($output, 400);
    }

}, ["host", "database", "username", "password"]);


$install = new Template(INCLUDES["TPL"]."/installer.tpl");
$install->styles = file_get_contents(SRC."/views/assets/css/phroses.css");
$install->script = file_get_contents(SRC."/views/assets/js/phroses.min.js");
echo $install;
