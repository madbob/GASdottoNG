@include('commons.textarea', ['obj' => $notification, 'name' => 'content', 'label' => 'Contenuto'])
@include('commons.boolfield', ['obj' => $notification, 'name' => 'mailed', 'label' => 'Invia Mail'])
