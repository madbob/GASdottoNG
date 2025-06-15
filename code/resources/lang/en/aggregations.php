<?php

return array (
  'all' => 'Aggregations',
  'limit_access' => 'Limit access',
  'help' => 
  array (
    'limit_access_to_order' => 'Flagging one or more items, the Order will be accessible only to Users assigned to those Groups. If none is flagged, the Order is accessible by everybody.',
    'permit_selection' => 'Flagging one or more items, Users can select one of them when submitting a new booking.',
    'context' => '<ul><li>User: Groups in this Aggregation will be assignable to each user, and are applied to each Orders</li><li>Booking: Groups in this Aggregation will be assignable to each Booking by Users; this is useful to handle logistic, when multiple delivery places are available</li></ul>',
    'limit_access' => 'When selected, it will be possible to choose one or more Groups of this Aggregation within each Order. If so, the Order itself will be accessible only to Users assigned to the same Groups.',
    'no_user_aggregations' => 'There are still no Aggregations assignable to Users.',
  ),
  'permit_selection' => 'Allow selection',
  'context' => 'Context',
  'by_booking' => 'Booking',
  'cardinality' => 'Each User can be assigned to',
  'cardinality_one' => 'one Group',
  'cardinality_many' => 'different Groups',
  'user_selectable' => 'Selectable by User',
  'group' => 'Group',
  'empty_list' => 'There are no items to show.<br>Adding new items, it will be possible to organize users in different ways, so to separate bookings, handle delivery logistic, apply special modifiers and much more.',
  'name' => 'Aggregation',
);
