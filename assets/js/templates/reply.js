module.exports = function (comment) {
    return '<div class="comment-response">' +
        '<strong>' + comment.author + '</strong> ' + comment.text + '<br><small>' + comment.posted + '</small>' +
        '</div>';
};