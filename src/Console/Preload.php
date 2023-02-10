<?php
namespace OffbeatWP\AcfLayout\Console;

use OffbeatWP\Console\AbstractCommand;
use OffbeatWP\AcfLayout\Layout\Renderer;
use WP_Query;

class Preload extends AbstractCommand
{
    public const COMMAND = 'acf-layout:preload';

    public function execute($args, $argsNamed)
    {
        $posts = new WP_Query([
            'post_type' => offbeat('acf_page_builder')->getEnabledPostTypes(),
            'meta_query' => [
                [
                    'key' => 'page_layout_editor_enabled',
                    'value' => '1',
                ]
            ],
            'posts_per_page' => -1,
        ]);
        
        $total = $posts->post_count;
        $current = 1;

        if ($posts->have_posts()) {
            while ($posts->have_posts()) {
                $posts->the_post();

                Renderer::getLayoutFields(get_the_ID(), true);
                $this->log('Post preloaded: ' . get_the_ID() . ' (' . $current . '/' . $total . ')');

                $current++;
            }
        }

        $this->success('Posts preloaded');
    }
}
