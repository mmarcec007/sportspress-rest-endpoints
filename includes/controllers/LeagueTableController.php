<?php

class LeagueTableController extends WP_REST_Controller
{
    public function register_routes()
    {
        $version = '1';
        $namespace = 'sportspress-rest-endpoints/v' . $version;
        $base = 'league_table';

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
     * Route URL https://example-wordpress.com/wp-json/sportspress-rest-endpoints/v1/league_table/1
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response
     */
    public function get_item($request)
    {
        $params = $request->get_params();
        $id = $params['id'] ?? -1;

        if (!is_numeric($id)) {
            $id = wp_get_post_by_slug($id, 'sp_table')->ID;
        }

        $data = sp_get_league_table_data($id);

        if (!empty($data)) {
            return new WP_REST_Response( [
                'league_table_data' => [
                    'leagues' => sp_get_leagues($id, false),
                    'seasons' => sp_get_seasons($id, false),
                    "table" => $data,
                ]
            ], 200 );
        }

        return new WP_Error( 'code', __( 'message', 'text-domain' ) );
    }

    // sp_get_table(0);
}