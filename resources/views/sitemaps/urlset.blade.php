{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
@foreach($urls as $u)
<url>
<loc>{{ $u['loc'] }}</loc>
@if(!empty($u['lastmod']))<lastmod>{{ $u['lastmod'] }}</lastmod>@endif
@if(isset($u['priority']))<priority>{{ $u['priority'] }}</priority>@endif
@if(!empty($u['changefreq']))<changefreq>{{ $u['changefreq'] }}</changefreq>@endif
@if(!empty($u['image']))
<image:image>
<image:loc>{{ $u['image'] }}</image:loc>
@if(!empty($u['image_title']))<image:title><![CDATA[{{ $u['image_title'] }}]]></image:title>@endif
</image:image>
@endif
</url>
@endforeach
</urlset>
