<?php 

use \reqc\Output;
use \phyrex\Template;
use function \Phroses\handleMethod;

use const \Phroses\{ DEPS, ROOT, SRC, SCHEMAVER, INCLUDES };
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

        // setup database
        $db = new PDO("mysql:host=".$_POST["host"].";dbname=".$_POST["database"], $_POST["username"], $_POST["password"]);
        if(version_compare($db->query("select version()")->fetchColumn(), DEPS["MYSQL"], "<")) throw new Exception("version");
        $schema = new Template(SRC."/schema/install.sql");
        $schema->schemaver = SCHEMAVER;
        $db->query($schema);

        // setup configuration file
        $c = new Template(SRC."/phroses.conf");
        $c->mode = (INPHAR) ? "production" : "development";
        $c->host = $_POST["host"];
        $c->username = $_POST["username"];
        $c->password = $_POST["password"];
        $c->database = $_POST["database"];
        $c->pepper = bin2hex(openssl_random_pseudo_bytes(10));

        file_put_contents(ROOT."/phroses.conf", $c);
        chown(ROOT."/phroses.conf", posix_getpwuid(posix_geteuid())['name']);
        chmod(ROOT."/phroses.conf", 0775);

        $out->send(["type" => "success"], 200);

    } catch(Exception $e) {
        $output = [ "type" => "error", "error" => "credentials" ];

        if($e->getMessage() == "version") {
            $output["error"] = "version";
            $output["minver"] = DEPS["MYSQL"];
        }

        $out->send($output, 400);
    }

}, ["host", "database", "username", "password"]);


$installer = new Template(INCLUDES["TPL"]."/installer.tpl");
$installer->styles = file_get_contents(SRC."/views/assets/css/main.css");
$installer->script = file_get_contents(SRC."/views/assets/js/install.js");
echo $installer;
