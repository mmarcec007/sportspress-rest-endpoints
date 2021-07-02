<?php

class VenuesController extends WP_REST_Controller
{
    public function register_routes()
    {
        $version = '1';
        $namespace = 'sportspress-rest-endpoints/v' . $version;
        $base = 'venues';

        register_rest_route( $namespace, '/' . $base, array(
            array (
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'args'                => array()
            )
        ));

        register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)', array(
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
        return new WP_REST_Response( ["venues" => $this->getVenuesOrSingleVenue()], 200 );
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
        $data = $this->getVenuesOrSingleVenue($id);

        if (!empty($data)) {
            return new WP_REST_Response( ["venue" => $data], 200 );
        }

        return new WP_Error( 'code', __( 'message', 'text-domain' ) );
    }

    /**
     * Gets the venues if no id was specified. In case you specify an ID you get the single venue.
     * @param null $id
     * @return array
     */
    private function getVenuesOrSingleVenue($id = null): array
    {
        $venue = [];
        $data = [];
        $args = array(
            'taxonomy'               => 'sp_venue',
            'orderby'                => 'name',
            'order'                  => 'ASC',
            'hide_empty'             => false
        );

        if (is_numeric($id)) {
            $args = array(
                'taxonomy' => 'sp_venue',
                'term_taxonomy_id' => intval($id),
                'hide_empty' => false
            );
        }

        $the_query = new WP_Term_Query($args);

        foreach ($the_query->get_terms() as $term) {
            $venueLocation = get_option('taxonomy_' . $term->term_id, true);
            $venue = [
                'id' => $term->term_id,
                'name' => $term->name,
                'description' => $term->description,
                'venue_location' => $venueLocation
            ];

            array_push($data, $venue);
        }

        if (count($data) === 1) {
            return $venue;
        } else if (count($data) > 1) {
            return $data;
        } else {
            return [];
        }
    }
}