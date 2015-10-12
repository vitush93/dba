var $ = require('jquery');

var CommentTemplate = require('../templates/comment');
var ReplyTemplate = require('../templates/reply');

var Comments = {

    $container: null,

    $comment: null,

    bindPost: function () {
        var _this = this;
        this.$comment.click(function () {
            var comment = _this.$text.val();

            $.get('/?text=' + comment + '&do=comment', function (data) {
                _this.$container.prepend(CommentTemplate(data));

                _this.$text.val('');
            });
        });
    },

    bindReply: function () {
        $('body').on('keyup', '.js-reply', function (e) {
            if (e.keyCode == 13) {
                var text = $(this).val();
                var comment = $(this).data('comment');

                var $this = $(this);
                $.get('/?text=' + text + '&comment=' + comment + '&do=reply', function (data) {
                    $this.parents('.comment-responses').find('.js-reply-container').append(ReplyTemplate(data));
                });
                $this.val('');
            }
        });
    },

    init: function () {
        this.$container = $('#js-comments');
        this.$comment = $('#js-comment');
        this.$text = $('#new-comment');

        this.bindPost();
        this.bindReply();
    }
};

module.exports = function () {
    Comments.init();
};
