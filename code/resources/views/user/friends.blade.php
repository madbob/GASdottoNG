<div class="row">
    <div class="col">
        @include('commons.addingbutton', [
            'user' => null,
            'template' => 'friend.base-edit',
            'typename' => 'friend',
            'typename_readable' => __('user.friend'),
            'targeturl' => 'friends',
            'extra' => [
                'creator_id' => $user->id,
            ]
        ])
    </div>
</div>

<hr>

<div class="row">
    <div class="col">
        @include('commons.loadablelist', [
            'identifier' => 'friend-list',
            'items' => $user->friends,
            'empty_message' => __('user.empty.friends'),
            'url' => 'users'
        ])
    </div>
</div>
