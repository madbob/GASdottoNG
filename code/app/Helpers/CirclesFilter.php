<?php

/*
    Qui vengono concentrate le funzioni usate per ordinare le prenotazioni degli
    ordini in funzioni di gruppi e cerchie.
    Usate in fase di stampa dei documenti esportati
*/

namespace App\Helpers;

use Illuminate\Support\Collection;

use App\Circle;

class CirclesFilter
{
    private $mode;

    private $circles;

    private $groups;

    public function __construct($aggregate, $request)
    {
        $this->circles = [];
        $this->groups = [];

        if (is_null($aggregate) == false) {
            $all = $aggregate->circlesByGroup();
            if (empty($all) == false) {
                $selected = null;

                foreach ($all as $group_id => $circles) {
                    if ($group_id == 'circles_master_sorting') {
                        continue;
                    }

                    $this->groups[$group_id] = [];

                    $key = sprintf('circles_%s', $group_id);
                    $selected = $request[$key] ?? 'all_by_name';

                    if ($selected != 'all_by_name' && $selected != 'all_by_place') {
                        $circle = Circle::find($selected);
                        if ($circle) {
                            $this->circles[] = $circle;
                            $this->groups[$group_id][] = $circle;
                        }
                    }
                }

                if (count($all) > 1) {
                    $this->mode = $request['circles_master_sorting'] ?? 'all_by_name';
                }
                else {
                    $this->mode = $selected == 'all_by_place' ? 'all_by_place' : 'all_by_name';
                }
            }
        }
        else {
            $this->mode = 'all_by_name';
        }
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function sortedByUser()
    {
        return $this->mode == 'all_by_name';
    }

    public function printableName()
    {
        return implode(' - ', array_map(fn ($c) => $c->name, $this->circles));
    }

    public function combinations()
    {
        $ret = [];

        if ($this->mode == 'all_by_place') {
            $result = [[]];

            foreach ($this->groups as $key => $circles) {
                $append = [];

                foreach ($result as $group) {
                    foreach ($circles as $circle) {
                        $group[$key] = $circle;
                        $append[] = $group;
                    }
                }

                $result = $append;
            }

            foreach ($result as $res) {
                $c = new CirclesFilter(null, null);
                $c->mode = $this->mode;
                $c->groups = $res;
                $c->circles = array_reduce($res, fn ($carry, $circles) => array_merge($carry, $circles), []);
                $ret[] = $c;
            }
        }
        else if (str_starts_with($this->mode, 'group_')) {
            $group_id = substr($this->mode, strlen('group_'));

            $c = new CirclesFilter(null, null);
            $c->mode = $this->mode;
            $c->groups = [$this->groups[$group_id]];
            $c->circles = $this->groups[$group_id];
            $ret[] = $c;
        }
        else {
            throw new \Exception('La combinazione di gruppi non dovrebbe essere usata se non quando si ordina per cerchie', 1);
        }

        return $ret;
    }

    private function sortByUserName($bookings)
    {
        return $bookings->sortBy(fn ($b) => $b->user->printableName());
    }

    private function sortByCircle($bookings)
    {
        return $bookings->sortBy(fn ($b) => $this->bookingSorting($b));
    }

    public function bookingSorting($booking)
    {
        $circles = $booking->involvedCircles();

        if (str_starts_with($this->mode, 'group_')) {
            $group = substr($this->mode, strlen('group_'));
            $circles = $circles->filter(fn($c) => $c->group_id == $group);
        }
        else {
            $circles = $circles->sortBy('group_id');
        }

        return $circles->map(fn ($c) => $c->name)->join(' - ');
    }

    public function sortBookings($bookings)
    {
        $tmp_bookings = new Collection();
        $filter_circles = (empty($this->circles) == false);

        foreach ($bookings as $booking) {
            $valid = true;

            if ($filter_circles) {
                $mycircles = $booking->involvedCircles();

                foreach ($this->circles as $required_circle) {
                    if (is_null($mycircles->firstWhere('id', $required_circle->id))) {
                        $valid = false;
                        break;
                    }
                }
            }

            if ($valid) {
                $tmp_bookings->push($booking);
            }
        }

        if ($this->mode == 'all_by_place' || str_starts_with($this->mode, 'group_')) {
            $bookings = $this->sortByCircle($tmp_bookings);
        }
        else {
            $bookings = $this->sortByUserName($tmp_bookings);
        }

        return $bookings;
    }
}
