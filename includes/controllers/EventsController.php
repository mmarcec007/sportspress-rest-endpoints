<?php

class EventsController extends WP_REST_Controller
{
    public function register_routes()
    {
        $version = '1';
        $namespace = 'sportspress-rest-endpoints/v' . $version;
        $base = 'events';

        register_rest_route( $namespace, '/' . $base, array(
            array (
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'args'                => array()
            )
        ));

        register_rest_route( $namespace, '/' . $base . '/(?P<id>[a-zA-Z0-9-]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_item' ),
                'args'                => array(
                    'context' => array(
                        'default' => 'view',
                    ),
                ),
            )
        ) );
    }

    /**
     * Route URL https://example-wordpress.com/wp-json/sportspress-rest-endpoints/v1/venues
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_items($request): WP_REST_Response
    {
        $query_params = $request->get_query_params();
        return new WP_REST_Response( ["events" => $this->getEventsOrSingleEvent(null, $query_params)], 200 );
    }

    /**
     * Route URL https://example-wordpress.com/wp-json/sportspress-rest-endpoints/v1/venues/1
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response
     */
    public function get_item($request)
    {
        $params = $request->get_params();
        $id = $params['id'] ?? -1;
        $data = $this->getEventsOrSingleEvent($id, null);

        if (!empty($data)) {
            return new WP_REST_Response( ["event" => $data], 200 );
        }

        return new WP_Error( 'code', __( 'message', 'text-domain' ) );
    }

    /**
     * Gets the venues if no id was specified. In case you specify an ID you get the single venue.
     * @param null $id
     * @return array
     */
    private function getEventsOrSingleEvent($id = null, $queryParams = null): array
    {
        $args = array();
        $data = [];
        if ($id !== null) {
            if (is_numeric($id)) {
                $args['post__in'] = [$id];
            } else {
                $args['name'] = $id;
            }
            $args['post_status'] = array('publish', 'future');
        } else {
            if (!empty($queryParams)) {
                if (isset($queryParams['post_status'])) {
                    if ($queryParams['post_status'] === 'publish') {
                        $args['orderby'] = 'date';
                        $args['order'] = 'DESC';
                    } else if ($queryParams['post_status'] === 'future') {
                        $args['orderby'] = 'date';
                        $args['order'] = 'ASC';
                    }
                    $args['post_status'] = $queryParams['post_status'];
                }
                if ($queryParams['posts_per_page']) {
                    $args['posts_per_page'] = $queryParams['posts_per_page'];
                }
                if ($queryParams['paged']) {
                    $args['paged'] = $queryParams['paged'];
                }
                if ($queryParams['team_slug']) {
                    $sp_team_id = wp_get_post_by_slug($queryParams['team_slug'], 'sp_team')->ID;
                    $args['meta_query'] = array(
                        array(
                            'key' => 'sp_team',
                            'value' => $sp_team_id,
                            'compare' => 'IN'
                        )
                    );
                }
            }
        }

        $sp_posts = sp_get_posts('sp_event', $args);

        foreach ($sp_posts as $sp_post) {
            $sp_status = get_post_meta($sp_post->ID, 'sp_status', true);
            $sp_day = get_post_meta($sp_post->ID, 'sp_day', true);
            $sp_format = get_post_meta($sp_post->ID, 'sp_format', true);
            $sp_event_status = sp_get_status($sp_post->ID);
            $sp_event_leagues = sp_get_leagues($sp_post->ID, false);
            $sp_event_venues = sp_get_venues($sp_post->ID, false);
            $sp_event_teams = sp_get_teams($sp_post->ID);

            $sp_event = [
                "id" => $sp_post->ID,
                "name" => $sp_post->post_name,
                "title" => $sp_post->post_title,
                "match_day" => $sp_day,
                "date" => sp_get_date($sp_post->ID),
                "time" => $sp_status === 'ok' ? sp_get_time($sp_post->ID) : $sp_status,
                "sp_event_status" => $sp_event_status,
                "format" => $sp_format,
            ];

            if (!empty($sp_event_leagues)) {
                $sp_event['leagues'] = $sp_event_leagues;
            }

            if (!empty($sp_event_teams)) {
                if (isset($sp_event_teams[0])) {
                    $sp_event['home_team'] = [
                        "id" => $sp_event_teams[0],
                        "name" => sp_get_team_name($sp_event_teams[0]),
                        "logo" => sp_get_logo_url($sp_event_teams[0])
                    ];
                }

                if (isset($sp_event_teams[1])) {
                    $sp_event['away_team'] = [
                        "id" => $sp_event_teams[1],
                        "name" => sp_get_team_name($sp_event_teams[1]),
                        "logo" => sp_get_logo_url($sp_event_teams[1])
                    ];
                }
            }

            if (!empty($sp_event_venues)) {
                $sp_event_venues_with_location = [];
                foreach ($sp_event_venues as $sp_event_venue) {
                    $event_venue["venue_info"] = $sp_event_venue;
                    if ($id !== null) {
                        $event_venue["venue_location"] = get_option('taxonomy_' . $sp_event_venue->term_id, true);
                    }

                    array_push($sp_event_venues_with_location, $event_venue);
                }
                $sp_event['sp_event_venues_with_location'] = $sp_event_venues_with_location;
            }

            if (sp_get_status($sp_post->ID) === 'results') {
                $sp_results = sp_get_results($sp_post->ID);
                $sp_event['results'] = $sp_results;
            }

            if ($id !== null) {
                $_thumbnail_id = get_post_meta($sp_post->ID, '_thumbnail_id', true);
                if (!empty($_thumbnail_id)) {
                    $sp_event['featured_image'] = wp_get_attachment_thumb_url($_thumbnail_id);
                }

                $sp_event['event_performance'] = sp_get_performance($sp_post->ID);
                $sp_event['content'] = $sp_post->post_content;
                $sp_event['excerpt'] = $sp_post->post_excerpt;
                $sp_event['time_line'] = get_post_meta($sp_post->ID, 'sp_timeline', true);
                $sp_event['video'] = get_post_meta($sp_post->ID, 'sp_video', true);
            }

            array_push($data, $sp_event);
        }

        if ($id !== null) {
            return $data[0];
        }

        return $data;
    }
}