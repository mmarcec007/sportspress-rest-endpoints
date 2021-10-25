=== Sportspress REST Endpoints ===
Contributors: mmarcec007
Tags: api,REST
Requires PHP: 7.4

This plugin exposes the data created via Sportspress in custom REST endpoints.

Currently Available Endpoints:
* Venues
  * yoursite.com/wp-json/sportspress-rest-endpoints/v1/venues
    * get all venues
  * yoursite.com/wp-json/sportspress-rest-endpoints/v1/venues/1
    * get venue details
* Events
  * yoursite.com/wp-json/sportspress-rest-endpoints/v1/events
    * get all events
  * yoursite.com/wp-json/sportspress-rest-endpoints/v1/events?post_status=publish
    * get current and finished games
  * yoursite.com/wp-json/sportspress-rest-endpoints/v1/events?post_status=future
    * get upcomming games
  * yoursite.com/wp-json/sportspress-rest-endpoints/v1/events/team1-vs-team2
    * get event details

== Installation ==
* Just clone the repository
* compress zip file
* and install it in your WordPress installation