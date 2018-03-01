var mode = document.currentScript.getAttribute("data-mode") || "page";

if(mode === "installer") require("./install");
else {
    require("./page");
    require("./uploads");
}