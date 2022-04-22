<?php

class TeamsController extends WP_REST_Controller
{
    public function register_routes()
    {
        $version = '1';
        $namespace = 'sportspress-rest-endpoints/v' . $version;
        $base = 'teams';

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
     * Route URL https://example-wordpress.com/wp-json/sportspress-rest-endpoints/v1/teams
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_items($request): WP_REST_Response
    {
        return new WP_REST_Response( ["teams" => $this->getTeamsOrSingleTeam()], 200 );
    }

    /**
     * Route URL https://example-wordpress.com/wp-json/sportspress-rest-endpoints/v1/teams/1
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response
     */
    public function get_item($request)
    {
        $params = $request->get_params();
        $id = $params['id'] ?? -1;
        $data = $this->getTeamsOrSingleTeam($id);

        if (!empty($data)) {
            return new WP_REST_Response( ["team" => $data], 200 );
        }

        return new WP_Error( 'code', __( 'message', 'text-domain' ) );
    }

    /**
     * Gets the venues if no id was specified. In case you specify an ID you get the single venue.
     * @param null $id
     * @return array
     */
    private function getTeamsOrSingleTeam($id = null): array
    {
        $data = [];
        $args = [];

        if ($id !== null) {
            if (is_numeric($id)) {
                $args['post__in'] = [$id];
            } else {
                $args['name'] = $id;
            }
            $args['post_status'] = array('publish');
        } else {
            if (!empty($queryParams)) {
                if ($queryParams['posts_per_page']) {
                    $args['posts_per_page'] = $queryParams['posts_per_page'];
                }
                if ($queryParams['paged']) {
                    $args['paged'] = $queryParams['paged'];
                }
            }
        }

        $sp_posts = sp_get_posts('sp_team', $args);
        $posts_count = count($sp_posts);
        foreach ($sp_posts as $sp_post) {
            if ($id !== null) {
                $sp_team = new SP_Team($sp_post->ID);
                $sp_player_list = new SP_Player_List($sp_post->ID);
                $players_list = $sp_player_list->data();
                $players = [];
                foreach ($players_list as $key => $value) {
                    if (is_numeric($key) && $key != 0) {
                        $sp_player = new SP_Player($key);
                        $players[] = $sp_player->get_post_data();
                    }
                }
                $data[] = [
                    "id" => $sp_post->ID,
                    "name" => $sp_post->post_title,
                    "content" => $sp_post->post_content,
                    "staff" => $sp_team->staff(),
                    "players" => $players
                ];
            } else {
                $data[] = [
                    "id" => $sp_post->ID,
                    "name" => $sp_post->post_title
                ];
            }
        }

        if (count($data) === 1) {
            return $data[0];
        } else if (count($data) > 1) {
            return $data;
        } else {
            return [];
        }
    }
}