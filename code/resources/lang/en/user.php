<?php

return array (
  'help' => 
  array (
    'suspended' => 'Suspended and Ceased users can not access to the platform, but are still registered. It is required to specify a date of ceasing/suspension.',
    'wrong_control_error' => 'Control code is wrong',
    'existing_email_error' => 'E-mail address already registered.',
    'duplicated_name_error' => 'Those first and last name are already present in the database. Please contact the administrator for more information.',
    'waiting_approval' => 'This user is waiting for approval!',
    'promote_friend' => 'By clicking "Save", this user will become a regular user. They will be assigned the role of %s, will have their own accounting, and will no longer be managed by %s. Their booking history will be preserved, but all of their previous payments will remain charged to %s.',
    'promote_friend_enforce_mail' => 'You must specify at least one contact email address of the new user',
    'reassign_friend' => 'From here, you can assign a friend to another user. All previous payments will remain charged to %s.',
    'notifications_instructions' => 'Select suppliers for which you want to receive a notification when new orders are opened.',
    'fee' => 'Data relating to the user\'s membership fee, which expires each year. To disable this option, go to Configurations -> Accounting',
    'deposit' => 'Data relating to the deposit paid by the user when registering for the GAS. To disable this option, go to Configurations -> Accounting',
    'satispay' => 'From here you can increase your credit using Satispay. Specify how much you want to pay and any notes for the administrators, you will receive a notification on your smartphone, to be confirmed within 15 minutes.',
    'remove_profile' => 'Do you really want to delete this account? All personal data will be anonymized, although your booking history will be preserved.',
    'remove_profile_credit_notice' => 'Before proceeding, it is advisable to contact the GAS representatives to settle the credit accounts.',
    'importing' => 
    array (
      'user' => 
      array (
        'balance' => 'Warning! Use this attribute only during the initial user import, and only for new users, or the balances will always be inconsistent!',
      ),
    ),
  ),
  'firstname' => 'Name',
  'lastname' => 'Surname',
  'change_friend' => 'Modify Friend',
  'formatted_aggregation' => 'Aggregation :name',
  'sepa' => 
  array (
    'mandate' => 'SEPA Mandate',
    'date' => 'SEPA Mandate Date',
    'intro' => 'SEPA Configuration',
    'help' => 'Please specify the parameters for generating RIDs for this user here. RIDs will not be generated for users for whom these fields have not been completed.',
    'identifier' => 'SEPA Mandate Identification',
  ),
  'last_login' => 'Last access',
  'last_booking' => 'Last reservation',
  'member_since' => 'Member since',
  'birthplace' => 'Birth place',
  'birthdate' => 'Birthday',
  'other_bookings' => 'Other bookings',
  'fullname' => 'Full Name',
  'taxcode' => 'Fiscal code',
  'card_number' => 'Card number',
  'payment_method' => 'Payment Method',
  'all' => 'Users',
  'payed_fee' => 'Paid fee',
  'name' => 'User',
  'address_part' => 
  array (
    'street' => 'Address (Street)',
    'city' => 'Address (City)',
    'zip' => 'Address (postal code)',
  ),
  'statuses' => 
  array (
    'active' => 'Active',
    'suspended' => 'Suspended',
    'deleted' => 'Removed',
    'removed' => 'Deleted',
  ),
  'friend' => 'Friend',
  'removed_user' => 'Removed User',
  'booking_friend_header' => 'Has ordered :amount',
  'pending_deliveries' => 'Today, this person has other bookings to receive:',
  'without_aggregation' => 'No Aggregations',
  'aggregation' => 'User Aggregation',
  'credit_below_zero' => 'Credit < 0',
  'fee_not_payed' => 'Unpaid Fee',
  'personal_data' => 'Personal Informations',
  'approve' => 'Approve',
  'do_not_approve' => 'Do not Approve and Remove',
  'family_members' => 'People in Family',
  'promote_friend' => 'Promote to regular user',
  'reassign_friend' => 'Change assignation',
  'change_friend_assignee' => 'New assignee',
  'fee' => 'Membership Fee',
  'deposit' => 'Deposit',
  'fees_status' => 'Fees\' Status',
  'all_ceased' => 'Ceased',
  'notices' => 
  array (
    'new_user' => 'New user registered on :gasname:',
    'pending_approval' => 'There\'s a new user awaiting review: just head to the admin panel to approve or delete.',
  ),
  'last_fee' => 'Last Fee',
  'fees' => 
  array (
    'new' => 'New Fee',
    'change' => 'Edit Fee',
  ),
  'empty' => 
  array (
    'friends' => 'Add here information about friends for whom you want to create sub-reservations. Each reservation will be autonomous but handled as a single one when delivering. Each friend can have his or her authentication credentials to access GASdotto and add reservations on his/her own.',
  ),
  'satispay' => 
  array (
    'reload' => 'Charge Credit with Satispay',
  ),
  'remove_profile' => 'Delete profile',
  'assign_aggregations' => 'Assign Aggregations',
);
