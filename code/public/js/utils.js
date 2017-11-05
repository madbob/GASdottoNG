function inlineFeedback(button, feedback_text) {
    var idle_text = button.text();
    button.text(feedback_text);
    setTimeout(function() {
        button.text(idle_text).prop('disabled', false);
    }, 2000);
}
