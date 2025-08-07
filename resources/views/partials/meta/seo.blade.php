{!! (new \App\Support\Robots())->metaTag() !!}

<link rel="canonical" href="{!! \Artesaos\SEOTools\Facades\SEOMeta::getCanonical() !!}" />
<meta name="description" content="{!! \Artesaos\SEOTools\Facades\SEOMeta::getDescription() !!}" />

{!! OpenGraph::generate() !!}
{!! Twitter::generate() !!}
{!! JsonLd::generate() !!}

@include('partials.meta.json-ld')
