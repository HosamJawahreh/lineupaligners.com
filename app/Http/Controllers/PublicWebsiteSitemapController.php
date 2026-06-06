<?php



namespace App\Http\Controllers;



use App\Services\SmilizPageRegistry;

use App\Services\WebsiteContent;

use App\Services\WebsiteLocale;

use Illuminate\Http\Response;



class PublicWebsiteSitemapController extends Controller

{

    public function __invoke(SmilizPageRegistry $pages, WebsiteLocale $locale, WebsiteContent $website): Response

    {

        if (! ($website->all()['published'] ?? false)) {

            abort(404);

        }



        $entries = [];



        foreach ($locale->enabled() as $code => $meta) {

            $entries[] = [

                'loc' => $locale->homeUrl($code),

                'priority' => '1.0',

                'changefreq' => 'weekly',

            ];



            foreach ($pages->resolvedSettings() as $key => $page) {

                if (! $page['enabled']) {

                    continue;

                }



                if (in_array($key, ['service-details', 'blog-single-details', 'case-study-style-1'], true)) {

                    continue;

                }



                $entries[] = [

                    'loc' => $pages->pageUrl($key, $code),

                    'priority' => '0.8',

                    'changefreq' => 'monthly',

                ];

            }



            $content = $website->all($code);



            foreach ($content['features'] ?? [] as $feature) {

                if (! filled($feature['slug'] ?? null)) {

                    continue;

                }



                $entries[] = [

                    'loc' => $website->serviceDetailUrl($feature, $code),

                    'priority' => '0.7',

                    'changefreq' => 'monthly',

                ];

            }



            foreach ($content['blog']['items'] ?? [] as $post) {

                if (! filled($post['slug'] ?? null)) {

                    continue;

                }



                $entries[] = [

                    'loc' => $website->blogPostUrl($post, $code),

                    'priority' => '0.7',

                    'changefreq' => 'monthly',

                ];

            }



            foreach ($website->publishedCaseStudies() as $case) {

                if (! filled($case['slug'] ?? null)) {

                    continue;

                }



                $entries[] = [

                    'loc' => $website->caseStudyDetailUrl($case, $code),

                    'priority' => '0.7',

                    'changefreq' => 'monthly',

                ];

            }

        }



        return response()

            ->view('website.sitemap', ['entries' => $entries])

            ->header('Content-Type', 'application/xml');

    }

}

