<?php

/*
    Qui vengono concentrate le funzioni usate per ordinare le prenotazioni degli
    ordini in funzioni di gruppi e cerchie.
    Usate in fase di stampa dei documenti esportati
*/

namespace App\Helpers;

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

                foreach($all as $group_id => $circles) {
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

    public function printableName()
    {
        return join(' - ', array_map(fn($c) => $c->name, $this->circles));
    }

    public function combinations()
    {
        if ($this->mode == 'all_by_place') {
            $result = [[]];

            foreach($this->groups as $key => $circles) {
                $append = [];

                foreach($result as $group) {
                    foreach($circles as $circle) {
                        $group[$key] = $circle;
                        $append[] = $group;
                    }
                }

                $result = $append;
            }

            $ret = [];

            foreach($result as $res) {
                $c = new CirclesFilter(null, null);
                $c->mode = $this->mode;
                $c->groups = $res;
                $c->circles = array_reduce($res, fn($carry, $circles) => array_merge($carry, $circles), []);
                $ret[] = $c;
            }

            return $ret;
        }
        else {
            throw new \Exception("La combinazione di gruppi non dovrebbe essere usata se non quando si ordina per cerchie", 1);
        }
    }

    private function sortByUserName($bookings)
    {
        return $bookings->sortBy(fn($b) => $b->user->printableName());
    }

    private function sortByCircle($bookings)
    {
        return $bookings->sortBy(fn($b) => $b->circles_sorting);
    }

    public function sortBookings($bookings)
    {
        $actual_circles = $this->circles;

        if (empty($this->circles)) {
            $tmp_bookings = collect($bookings);
        }
        else {
            $tmp_bookings = new Collection();

            foreach($bookings as $booking) {
                $mycircles = $booking->involvedCircles();
                $valid = true;

                foreach($this->circles as $required_circle) {
                    if (is_null($mycircles->firstWhere('id', $required_circle->id))) {
                        $valid = false;
                        break;
                    }
                }

                if ($valid) {
                    $tmp_bookings->push($booking);
                }
            }
        }

        if ($this->mode == 'all_by_place') {
            $bookings = $this->sortByCircle($tmp_bookings);
        }
        else {
            $bookings = $this->sortByUserName($tmp_bookings);
        }

        return $bookings;
    }
}
