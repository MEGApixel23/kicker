<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GameTest extends TestCase {

	use DatabaseTransactions;

	protected $users = [];
	protected $points;

	/** @test */
	public function save_game_with_api() {
		$this->users = $this->create_users(4);

		$this->auth();
		$this->points = rand(0,9);
		$response = $this->create_game_request($this->points);

		$response
			->seeJson()
			->seeJsonStructure(['id']);


		$this->seeInDatabase(
			'games',
			[
				'team_a_points' => 10,
				'team_b_points' => $this->points,
			]
		);
	}

	/** @test */
	public function check_games() {
		$this->request('GET', '/');
		$this->seeJson()->seeJsonStructure(['total', 'data']);
	}

	public function create_game_request($points) {
		return $this->request('POST', '/api/games', [
			'games_users_a' => [
				$this->users[0]->id,
				$this->users[1]->id
			],
			'team_a_points' => 10,
			'games_users_b' => [
				$this->users[2]->id,
				$this->users[3]->id
			],
			'team_b_points' => $points,
			'played_at' => Carbon::now()->format('m/d/Y H:i')
		]);
	}

}
