<?php
namespace MT\WP\Plugin\Api;

use MT\WP\Plugin\Common\MT_QueryBuilder;
use \WP_Error as WP_Error;
use \WP_REST_Controller as WP_REST_Controller;
use \WP_REST_Response as WP_REST_Response;

class RestController extends WP_Rest_Controller
{

	const HTTP_STATUS_200_OK = 200;
	const HTTP_STATUS_201_CREATED = 201;
	const HTTP_STATUS_400_BAD_REQUEST = 400;
	const HTTP_STATUS_404_NOT_FOUND = 404;
	const HTTP_STATUS_500_INTERNAL_ERROR = 500;

	public function register_routes()
	{
		$namespace = 'mt/v1';
		register_rest_route(
			$namespace,
			'/photographer',
			array(
				'methods' => 'GET',
				'callback' => array($this, 'get_items'),
			)
		);
		register_rest_route(
			$namespace,
			'/photographer/(?P<id>\d+)',
			array(
				'methods' => 'GET',
				'callback' => array($this, 'get_item'),
				'args' => array(
					'id' => array(
						'validate_callback' => function ($param, $request, $key) {
							return is_numeric($param) && strlen($param) <= 4; // db field length
						}
					)
				),
			)
		);
	}
	
	public function get_items($request)
	{
		$query = (new MT_QueryBuilder())
			->from('photographer')
			->select('wp_mt_photographer.id')
			->select('wp_mt_photographer.name')
			->select('wp_mt_photographer.date')
			->select('COUNT(wp_mt_photo.id) as num_photos')
			->joinLeft('photo', 'wp_mt_photo.photographer = wp_mt_photographer.id')
			->whereEqual('wp_mt_photo.show', 1)
			->groupBy('wp_mt_photographer.name')
			->orderBy('wp_mt_photographer.name')
			->limit(15);
		return new WP_REST_Response($query->getResult(), self::HTTP_STATUS_200_OK);
	}
	
	public function get_item($request)
	{
		$id = (int) $request['id'];
		$photographer = (new MT_Photographer($id))->getOne(array('id', 'name'));
		return new WP_REST_Response($photographer, self::HTTP_STATUS_200_OK);
	}
}
