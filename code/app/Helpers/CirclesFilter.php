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
        else {
            $this->mode = 'all_by_name';
        }
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function combinations()
    {
        if ($this->mode == 'all_by_place') {
            $result = [[]];

            foreach($this->groups as $key => $circles) {
                $append = [];

                foreach($result as $product) {
                    foreach($circles as $item) {
                        $product[$key] = $item;
                        $append[] = $product;
                    }
                }

                $result = $append;
            }

            return $result;
        }
        else {
            throw new \Exception("La combinazione di gruppi non dovrebbe essere usata se non quando si ordina per cerchie", 1);
        }
    }

    private function sortByUserName($bookings)
    {
        usort($bookings, function($a, $b) {
            return $a->user->printableName() <=> $b->user->printableName();
        });

        return $bookings;
    }

    private function sortByCircle($bookings)
    {
        usort($bookings, function($a, $b) {
            return $a->circles_sorting <=> $b->circles_sorting;
        });

        return $bookings;
    }

    public function sortBookings($bookings)
    {
        $actual_circles = $this->circles;

        if (empty($this->circles)) {
            $tmp_bookings = $bookings;
        }
        else {
            $tmp_bookings = [];

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
                    $tmp_bookings[] = $booking;
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
