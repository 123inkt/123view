<?php
declare(strict_types=1);

return [
    'login.cancelled'                             => 'The log in was cancelled',
    'login.not.successful'                        => 'The log in was not successful',
    'login.invalid.azuread.callback'              => 'Invalid AzureAd callback. The `code` argument is missing.',
    'login.unable.to.validate.login.attempt'      => 'Unable to validate the login attempt. Please retry',
    'login.authorization.has.no.token'            => 'The authorization token doesn\'t contain a username. Unable to login',
    'login.in.with.active.directory'              => 'Login in with Active Directory',
    'sign.in'                                     => 'Sign in',
    'rules'                                       => 'Rules',
    'logout'                                      => 'Logout',
    'accept'                                      => 'Accept',
    'raise.concern'                               => 'Raise concern',
    'reopen.review'                               => 'Reopen review',
    'close.review'                                => 'Close review',
    'resume.review'                               => 'Resume review',
    'rule.new'                                    => 'New rule',
    'open'                                        => 'Open',
    'closed'                                      => 'Closed',
    'on'                                          => 'on',
    'save'                                        => 'Save',
    'add.comment'                                 => 'Add comment',
    'resolved'                                    => 'Resolved',
    'resolve'                                     => 'Resolve',
    'click.to.resolve'                            => 'Click to resolve',
    'click.to.unresolve'                          => 'Click to mark as unresolved',
    'reply'                                       => 'Reply',
    'edit.comment'                                => 'Edit comment',
    'renamed.from'                                => 'renamed from',
    'delete.comment'                              => 'Delete comment',
    'confirm.delete.comment'                      => 'Are you sure you want to delete this comment thread?',
    'confirm.delete.reply'                        => 'Are you sure you want to delete this comment?',
    'leave.a.comment.on.line'                     => 'Leave a comment on line {line}',
    'leave.a.reply'                               => 'Leave a comment. Markdown is supported',
    'rule.successful.saved'                       => 'Rule successfully saved.',
    'active'                                      => 'Active',
    'inactive'                                    => 'Inactive',
    'notifications'                               => 'Notifications',
    'name'                                        => 'Name',
    'add.reviewer'                                => 'Add reviewer',
    'once-per-hour'                               => 'Once per hour',
    'once-per-two-hours'                          => 'Once per two hours',
    'once-per-three-hours'                        => 'Once per three hours',
    'once-per-four-hours'                         => 'Once per four hours',
    'once-per-day'                                => 'Once per day',
    'once-per-week'                               => 'Once per week',
    'add.recipient'                               => 'Add recipient',
    'At least {{ limit }} recipient is required'  => 'At least {{ limit }} recipient is required',
    'At most {{ limit }} recipients can be set'   => 'At most {{ limit }} recipients can be set',
    'At least {{ limit }} repository is required' => 'At least {{ limit }} repository is required',
    'At most {{ limit }} filters can be set'      => 'At most {{ limit }} filters can be set',
    'filter.type.file'                            => 'File (regex)',
    'mail.subject'                                => 'Mail subject',
    'filter.type.subject'                         => 'Subject (regex)',
    'filter.type.author'                          => 'Author',
    'upsource'                                    => 'Upsource',
    'darcula'                                     => 'Darcula',
    'add.filter'                                  => 'Add filter',
    'recipients'                                  => 'Recipients',
    'repository'                                  => 'Repository',
    'include.commits'                             => 'Include commits',
    'exclude.commits'                             => 'Exclude commits',
    'rule.options.subject.help'                   => 'The subject of the e-mail. If left empty will default to',
    'rule.options.subject.example'                => '[Commit Notification] New revisions for: {name}',
    'rule.options.subject.vars'                   => 'Supported variables: <code>{name}</code>, <code>{authors}</code>, and <code>{repositories}</code>.',
    'Email'                                       => 'Email',
    'Repositories'                                => 'Repositories',
    'Frequency'                                   => 'Frequency',
    'Pattern'                                     => 'Pattern',
    'Projects'                                    => 'Projects',
    'reviews'                                     => 'Reviews',
    'search.review'                               => 'Search code review',
    'Rule'                                        => 'Rule',
    'Filters'                                     => 'Filters',
    'Inclusions'                                  => 'Inclusions',
    'Theme'                                       => 'Theme',
    'Mail theme'                                  => 'Mail theme',
    'advanced'                                    => 'Advanced',
    'Ignore space at eol'                         => 'Ignore space at end-of-line',
    'Ignore space change'                         => 'Ignore space changes',
    'Ignore all space'                            => 'Ignore all space',
    'Ignore blank lines'                          => 'Ignore blank lines',
    'Diff algorithm'                              => 'Diff algorithm',
    'Diff whitespace options'                     => 'Diff whitespace options',
    'Not a valid e-mail address.'                 => 'Not a valid e-mail address.',
    'Not a valid regular expression.'             => 'Not a valid regular expression.',
    'cancel'                                      => 'Cancel',
    'Are you sure you want to delete this rule?'  => 'Are you sure you want to delete this rule?',
    'rule.removed.successful'                     => 'Rule {name} successfully removed.',
    'Options'                                     => 'Options',
    'rules.welcome.banner'                        => 'Welcome to git commit notification',
    'rules.welcome.description'                   => 'Receive notifications about one or more commits in one or more repositories.',
    'rules.add.first.rule'                        => 'Add your first rule',
    'rule.edit.title'                             => 'Edit rule',
    'rule.delete.title'                           => 'Delete rule',
    'page.title.single.sign.on'                   => 'Login',
    'Exclude merge commits'                       => 'Exclude merge commits',
    'redirect.access.denied.session.expired'      => 'Your session has expired.',
    'discussion.points.to.modified.code'          => 'Discussion is pointing towards a modified code fragment. Comment was originally at line {line}.',
    'all.reviews'                                 => 'All reviews',
    'open.reviews'                                => 'Open reviews',
    'closed.reviews'                              => 'Closed reviews',
    'review.search.hint'                          => 'Search any review, or for a id: <code>id:12345</code>, for state: <code>state:open|closed</code>, for author: <code>author:me|&lt;email&gt;|&lt;name&gt;</code>.',
    'overview'                                    => 'Overview',
    'revisions'                                   => 'Revisions',
    'files'                                       => 'Files',
    'reviewers'                                   => 'Reviewers',
    'author'                                      => 'Author',
    'accept.open.comments'                        => 'There are still {count} open comments.',
    'detach.revisions'                            => 'Detach selected',
    'review.no.authors'                           => 'No authors',
    'review.no.files'                             => 'No files',
    'review.no.revisions'                         => 'No revisions',
    'hash'                                        => 'Hash',
    'title'                                       => 'Title',
    'at'                                          => 'At',
    'review'                                      => 'Review',
    'search.revisions'                            => 'Search revision',
    'add.revisions'                               => 'Add revisions',
    'attach.to.review'                            => 'Attach to review',
    'revisions.added.to.review'                   => '{count} revision(s) added to review {review}',
    'revisions.skipped.to.add.to.review'          => '{count} revision(s) were not added to review {review}',
    'review.add.me'                               => 'Add me',
    'mail.new.comment.subject'                    => '[New comments] {reviewId}: {reviewTitle}',
    'mail.updated.comment.subject'                => '[Updated discussion] {reviewId}: {reviewTitle}',
    'mail.comment.resolved.subject'               => '[Resolved discussion] {reviewId}: {reviewTitle}',
    'mail.new.comment.by.user.on'                 => 'New comment by {userName} on',
    'mail.new.reply.by.user.on'                   => 'New reply by {userName} on',
    'mail.comment.was.resolved.on'                => 'Comment was resolved by {userName} on',
    'comment.mark.as.resolved'                    => '<em>{userName}</em> marked comment as resolved.',
    'mail.settings'                               => 'Code review notification settings',
    'mail.settings.save.successfully'             => 'Settings successfully saved',
    'settings'                                    => 'Settings',
    'commit.notification.rules'                   => 'Commit notification rules',
    'form.label.mail.comment.added'               => 'Receive a notification when a comment is added to a review you are author or reviewer on',
    'form.label.mail.comment.replied'             => 'Receive a notification when a reply is added to a comment thread you are participant of',
    'form.label.mail.comment.resolved'            => 'Receive a notification when a comment thread is resolved you are participant of'
];
