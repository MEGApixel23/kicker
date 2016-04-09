<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Game extends Model
{
    const TEAM_A_INDEX = 'a';
    const TEAM_B_INDEX = 'b';

    protected $table = 'games';
    protected $fillable = ['played_at', 'team_a_points', 'team_b_points'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function gamesUsersA()
    {
        return $this->hasMany(GameUser::class)->where('team_index', static::TEAM_A_INDEX);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function gamesUsersB()
    {
        return $this->hasMany(GameUser::class)->where('team_index', static::TEAM_B_INDEX);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function usersA()
    {
        $users = [];
        foreach ($this->gamesUsersA as $gamesUsers) {
            $users[] = $gamesUsers->user;
        }

        return $users;
    }

    public function usersB()
    {
        $users = [];
        foreach ($this->gamesUsersB as $gamesUsers) {
            $users[] = $gamesUsers->user;
        }

        return $users;
    }

    /**
     * @return float|int
     */
    public function getGameResult()
    {
        if ($this->team_a_points > $this->team_b_points) {
            return GameProcessor::WIN;
        } elseif ($this->team_a_points < $this->team_b_points) {
            return GameProcessor::LOSE;
        } else {
            return GameProcessor::DRAW;
        }
    }

    /**
     * @param $value
     * @return $this
     */
    public function setPlayedAtAttribute($value)
    {
        $this->attributes['played_at'] = date('c', strtotime($value));
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getPlayedAt()
    {
        return date('m/d/Y H:i', strtotime($this->attributes['played_at']));
    }

    protected function cancel(array $options = [])
    {
        $resetRating = isset($options['reset_rating']) ? (bool) $options['reset_rating'] : true;

        foreach ([&$this->gamesUsersA, &$this->gamesUsersB] as $gamesUsers) {
            while (true) {
                if ($gamesUsers->isEmpty())
                    break;

                $gameUser = $gamesUsers->shift();
                $user = $gameUser->user;
                $userFirstGameId = $user->getFirstGameId();

                if ($userFirstGameId === $this->id) {
                    $user->rating = GameProcessor::DEFAULT_RATING;
                } elseif ($resetRating) {
                    $user->rating = $gameUser->rating_before;
                }

                if (!$user->save()) {
                    // do something...
                }

                if (!$gameUser->delete()) {
                    // do something...
                }
            }
        }

        return $this;
    }

    protected function setUsers($teamAUsers, $teamBUsers)
    {
        if (!$this->gamesUsersA->isEmpty())
            return false;

        if (count($teamAUsers) === 0 || count($teamBUsers) === 0)
            return false;

        $gameResult = $this->getGameResult();

        list($teamARatings, $teamBRatings) = GameProcessor::calculateRatingForTeams(
            $teamAUsers, $teamBUsers, $gameResult
        );

        foreach ($teamAUsers as $user) {
            $gameUser = GameUser::create([
                'game_id' => $this->id,
                'user_id' => $user->id,
                'team_index' => $this::TEAM_A_INDEX,
                'rating_before' => $user->rating,
                'rating_after' => array_shift($teamARatings)
            ]);

            $user->rating = $gameUser->rating_after;

            if (!$user->save()) {
                // do something...
            }
        }

        foreach ($teamBUsers as $user) {
            $gameUser = GameUser::create([
                'game_id' => $this->id,
                'user_id' => $user->id,
                'team_index' => $this::TEAM_B_INDEX,
                'rating_before' => $user->rating,
                'rating_after' => array_shift($teamBRatings)
            ]);

            $user->rating = $gameUser->rating_after;

            if (!$user->save()) {
                // do something...
            }
        }

        return $this;
    }

    public function currentPosition()
    {
        return (int) Game::where('played_at', '<=', $this->played_at)
            ->where('id', '!=', (int) $this->id)
            ->orderBy('played_at', 'asc')
            ->count();
    }

    public static function findByPosition($position)
    {
        return Game::orderBy('played_at', 'asc')
            ->skip((int) $position);
    }

    public function recountFromPosition($position)
    {
        $games = Game::findByPosition($position)
            ->with(['gamesUsersA.user', 'gamesUsersB.user'])
            ->limit(100000000000)
            ->get();

        if (!$games->isEmpty()) {
            $games->each(function ($game, $key) use ($position) {
                $usersIdsA = $game->gamesUsersA->pluck('user_id')->all();
                $usersIdsB = $game->gamesUsersB->pluck('user_id')->all();

                if ($key === 0) {
                    $game->cancel(['reset_rating' => true]);
                } else {
                    $game->cancel(['reset_rating' => false]);
                }

                $usersATeam = User::whereIn('id', $usersIdsA)->get();
                $usersBTeam = User::whereIn('id', $usersIdsB)->get();

                $game->setUsers($usersATeam, $usersBTeam);
            });
        }

        User::updateStat();
    }

    public function updateWith($newAttributes)
    {
        $currentPosition = (int) $this->currentPosition();
        $this->fill($newAttributes);
        $newPosition = (int) $this->currentPosition();
        $this->save();

        $usersATeam = User::whereIn('id', $newAttributes['games_users_a'])->get();
        $usersBTeam = User::whereIn('id', $newAttributes['games_users_b'])->get();

        $this->cancel();
        $this->setUsers($usersATeam, $usersBTeam);
        $position = min([$currentPosition, $newPosition]);
        $prevPosition = $position - 1 >= 0 ? $position - 1 : 0;

        $this->recountFromPosition($prevPosition);

        return $this;
    }

    public static function create(array $attributes = [])
    {
        $self = parent::create($attributes);

        /* @var $self Game */
        $usersATeam = User::whereIn('id', $attributes['games_users_a'])->get();
        $usersBTeam = User::whereIn('id', $attributes['games_users_b'])->get();

        $self->setUsers($usersATeam, $usersBTeam);
        $position = $self->currentPosition();

        $prevPosition = $position - 1 >= 0 ? $position - 1 : 0;
        $self->recountFromPosition($prevPosition);

        return $self;
    }

    public function delete()
    {
        $position = $this->currentPosition();
        $this->cancel();
        $prevPosition = $position - 1 >= 0 ? $position - 1 : 0;

        $this->recountFromPosition($prevPosition);

        return parent::delete();
    }
}