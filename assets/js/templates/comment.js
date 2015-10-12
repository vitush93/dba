module.exports = function (comment) {
    return '<div class="panel panel-default comment">' +
        '<div class="panel-body">' +
            '<div class="comment-header">' +
                '<h4>' + comment.author + ' <small>' + comment.posted + '</small></h4>' +
            '</div>' +
            '<div class="comment-body">' +
                comment.text +
            '</div>' +
        '</div>' +
        '<div class="panel-footer comment-responses"><div class="js-reply-container"></div>' +
            '<textarea placeholder="write a response.." data-comment="' + comment.id + '" id="new-response" class="form-control js-reply"></textarea>' +
        '</div>' +
    '</div>';
};