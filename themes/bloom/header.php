<header>
    <h1 class="header_sitename">
        <a class="header_sitename_link" href="/"><?= Phroses\Phroses::$site->name; ?></a>
    </h1>
    
    <nav class="header_nav">
        <ul class="header_navlist navlist">
            <?php
            $items = [
                "Home" => "#",
                "About" => "#",
                "Contact" => "#",
                "Links" => "#",
                "Test" => "#"
            ];
            
            foreach($items as $k => $v) { ?>
                <li class="header navlist_item">
                    <a class="header navlist_link" href="<?= $v; ?>"><?= $k; ?></a>
                </li> <?php
            } ?>
        </ul>
    </nav>
    <div class="clear"></div>
</header>