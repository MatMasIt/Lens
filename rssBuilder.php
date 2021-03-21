<?php
function rss($a)
{
    ob_start();
    echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>

    <rss version="2.0">

        <channel>
            <title>Lens - Innovationplaylist.eu</title>
            <link>https://innovationplaylist.eu/lens</link>
            <description>Piattaforma di trasparenza di Innovationplaylist.eu</description>
            <author>mascarello.mattia@innovationplaylist.eu</author>
            <language>IT_it</language>
        </channel>
    <?php
        foreach($a as $e){
            echo "<item>";
            echo "\n<title><![CDATA[".($e["title"])."]]></title>\n";
            echo "<description><![CDATA[".($e["shortDescription"])."]]></description>\n";
            echo "<link>https://www.innovationplaylist.eu/lens/app/api/view/UID/".($e["UID"])."/render</link>\n";
            echo "<pubDate>".($e["publishedDate"])."</pubDate>";
            echo "</item>";
        }
    ?>
    </rss>
<?php
    return  ob_get_contents();
}
?>
