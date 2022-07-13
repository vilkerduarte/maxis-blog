<?php
$busca = getListDb('post');
if($busca){
  $list = [];
  $datetime = new DateTime(date('Y-m-d H:i:s'));
  $date = $datetime->format(DateTime::ATOM);
  $xml = '<?xml version="1.0" encoding="UTF-8"?>
  <urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
      <url>
        <loc>'.ENDERECO_SITE.'</loc>
        <lastmod>'.$date.'</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
      </url>';
  foreach($busca as $item){
    $datetime = new DateTime($item['data']);
    $date = $datetime->format(DateTime::ATOM);
    $xml .='
      <url>
          <loc>'.ENDERECO_SITE.'/post/'.$item['url'].'</loc>
          <lastmod>'.$date.'</lastmod>
          <changefreq>monthly</changefreq>
          <priority>'.(strstr($item['description'],'axisini') ? '1.00' : '0.9').'</priority>
      </url>    ';
  }
  $xml .= '
   </urlset>';
   file_put_contents(__DIR__."/../sitemap.xml",$xml);
}
erro(403);
exit;

    foreach($rows as $v){
        $datetime = new DateTime($v['updated_at']);
        $date = $datetime->format(DateTime::ATOM);
        $xml .='
        <url>
            <loc>'.HOME.'/'.$v['slug'].'</loc>
            <lastmod>'.$date.'</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.85</priority>
        </url>';
    }
$xml .= '
</urlset>';
