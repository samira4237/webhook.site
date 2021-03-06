<!DOCTYPE html>
<html ng-app="app" ng-controller="AppController">
<head>
    <title>Webhook Tester</title>
    <!-- Libraries -->
    <link href="assets/css/libs/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <script src="assets/scripts/libs/jquery-2.2.2.min.js"></script>
    <script src="assets/scripts/libs/angular.min.js"></script>
    <script src="assets/scripts/libs/angular-ui-router.js"></script>
    <script src="assets/scripts/libs/bootstrap.min.js"
            integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
            crossorigin="anonymous" async defer></script>
    <script src="assets/scripts/libs/clipboard.min.js"></script>
    <script src="assets/scripts/libs/bootstrap-notify.min.js"></script>
    
    <!-- App -->
    <script src="assets/scripts/app.js"></script>
    <link href="css/app.css" rel="stylesheet">
    <script>
        var AppConfig = {
            PusherToken: "<?=config('broadcasting.connections.pusher.key')?>",
        };
    </script>

    <!-- Pusher -->
    <script src="https://js.pusher.com/3.2/pusher.min.js"></script>

    <meta name="description" content="Easily test webhooks and HTTP requests with this handy tool that displays requests in realtime.">
</head>
<body>
<div class="mainView" ui-view>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/" ui-sref="home()">Webhook Tester</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <div class="nav navbar-left navbar-form">
                <a href="https://github.com/fredsted/webhook.site" target="_blank"
                   style="margin-top: 7px"
                   class="btn btn-xs btn-link">
                    Github Page</a>
                <a href="https://twitter.com/fredsted" target="_blank"
                   style="margin-top: 7px"
                   class="btn btn-xs btn-link">
                    @fredsted</a>
                <button style="margin-top: 7px"
                   class="openModal btn btn-xs btn-link"
                   data-modal="#helpModal">
                    Help</button>
            </div>
            <div class="nav navbar-right navbar-form">&nbsp;
                <div class="form-group">
                    <input id="tokenUrl" type="text" class="form-control click-select"
                           style="width: 200px;"
                           value="http://{{ domain }}/{{ token.uuid }}">
                </div>
                <button class="btn btn-success copyTokenUrl" data-clipboard-target="#tokenUrl">
                    <span class="glyphicon glyphicon-copy"></span> Copy</button> &nbsp;
                <button type="button" class="btn btn-primary openModal" data-modal="#newUrlModal">
                    <span class="glyphicon glyphicon-plus-sign"></span> New URL
                </button>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <p class="sidebar-header">Requests ({{ requests.data.length }})</p>

            <p ng-show="!hasRequests" class="small">
                <img src="assets/images/loader.gif"/>
                &nbsp; Waiting for first request...
            </p>

            <ul class="nav nav-sidebar">
                <li ng-repeat="(key, request) in requests.data"
                    ng-class="currentRequestIndex === request.uuid ? 'active' : ''">
                    <a ng-click="setCurrentRequest(request)" class="select">
                        <span class="label label-{{ getLabel(request.method) }}">{{ request.method }}</span>
                        #{{ request.uuid.substring(0,5) }} {{ request.ip }} <br/>
                        <small>{{ request.created_at }}</small>
                    </a>
                    <a ng-click="deleteRequest(request, key)" class="btn btn-danger delete">
                        X
                    </a>
                </li>
                <li ng-show="requests.next_page_url">
                    <a ng-click="getNextPage(token.uuid)" class="prevent-default">Load more</a>
                </li>
            </ul>
        </div>
        <div id="request" class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <div ng-show="!hasRequests">
                <p><strong>Webhook Tester</strong>
                    allows you to easily test webhooks and other types of HTTP requests.</p>
                <p>Any requests sent to that URL are logged here instantly
                    &mdash; you don't even have to refresh!</p>
                <hr>
                <p>Here's your unique URL that was created just now:</p>
                <p>
                    <code>http://{{ domain }}/{{ token.uuid }}</code>
                    <a class="btn btn-xs btn-link copyTokenUrl" data-clipboard-target="#tokenUrl">Copy</a>
                    <a class="btn btn-xs btn-link"
                       href="http://{{ domain }}/{{ token.uuid }}"
                       target="_blank">
                        <span class="glyphicon glyphicon-new-window"></span> Open in new tab</a>
                </p>
                <hr>
                <p>Bookmark this page to go back to the requests at any time.
                    For more info, click <b>Help</b>.</p>
                <p><a href="https://github.com/fredsted/webhook.site">Fork this on GitHub</a></p>
            </div>
            <div ng-show="hasRequests">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <label style="float: right"><input type="checkbox" ng-model="hideDetails"> Hide Request Details</label>
                        </div>
                    </div>
                    <div class="row" id="requestDetails" ng-show="!hideDetails">
                        <div class="col-md-4">
                            <table class="table table-borderless table-striped">
                                <tbody>
                                    <tr>
                                        <th colspan="2">Request Details</th>
                                    </tr>
                                    <tr>
                                        <td width="25%">URL</td>
                                        <td id="req-url"><a href="{{ currentRequest.url }}">{{ currentRequest.url }}</a></td>
                                    </tr>
                                    <tr>
                                        <td>Host</td>
                                        <td id="req-ip">{{ currentRequest.ip }}</td>
                                    </tr>
                                    <tr>
                                        <td>Date</td>
                                        <td id="req-date">{{ currentRequest.created_at | date:'shortTime' : 'UTC' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Method</td>
                                        <td id="req-method">{{ currentRequest.method }}</td>
                                    </tr>
                                    <tr>
                                        <td>Link</td>
                                        <td id="req-direct-link">
                                            <a href="http://{{ domain }}/#/{{ token.uuid }}/{{ currentRequestIndex }}/{{ currentPage }}">Direct link to request</a></td>
                                    </tr>
                                    <tr ng-show="hasRequests && currentRequest.content != '' && isValidJSON(currentRequest.content)">
                                        <td>Options</td>
                                        <td>
                                                <a class=""
                                                        ng-click="currentRequest.content = formatContentJson(currentRequest.content)"
                                                        style="">
                                                    Format JSON</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-borderless table-striped">
                                <tbody>
                                <tr>
                                    <th colspan="2">Headers</th>
                                </tr>
                                <tr ng-repeat="(headerName, values) in currentRequest.headers">
                                    <td width="25%">{{ headerName }}</td>
                                    <td><code ng-repeat="value in values">{{ (value == '' ? '(empty)' : value) }}{{$last ? '' : ', '}}</code></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <p ng-show="hasRequests && currentRequest.content == ''">
                                The request did not have any body content.</p>

                            <pre id="req-content"
                                 ng-show="hasRequests && currentRequest.content != ''"
                                 ng-bind="currentRequest.content"></pre>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="helpModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">What is Webhook Tester?</h4>
            </div>
            <div class="modal-body">
                <p><strong>Webhook Tester</strong>
                    allows you to easily test webhooks and other types of HTTP requests.</p>
                <p>Here's your unique URL:</p>
                <p>
                    <code>http://{{ domain }}/{{ token.uuid }}</code>
                    <a href="http://{{ domain }}/{{ token.uuid }}" target="_blank">(try it!)</a>
                </p>
                <p>Any requests sent to that URL are instantly logged here - you don't even have to refresh.</p>
                <p>
                    Append a status code to the url, e.g.: <br/>
                    <code>http://{{ domain }}/{{ token.uuid }}/404</code>, <br/>
                    so the URL will respond with a 404 Not Found.</p>
                <p>You can bookmark this page to go back to the request contents at any time.</p>
                <p><a href="https://github.com/fredsted/webhook.site">Fork this on GitHub</a></p>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" tabindex="-1" role="dialog" id="newUrlModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Configure URL</h4>
            </div>
            <div class="modal-body">
                <p>You have the ability to customize how your URL will respond by changing the
                    status code, content-type header and the content.</p>
                <hr>
                <form class="form-horizontal" id="createTokenForm">
                    <fieldset>

                        <!-- Text input-->
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="default_status">Default status code</label>
                            <div class="col-md-4">
                                <input id="default_status" name="default_status" type="text" placeholder="200" class="form-control input-md">

                            </div>
                        </div>

                        <!-- Text input-->
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="default_content_type">Content Type</label>
                            <div class="col-md-4">
                                <input id="default_content_type" name="default_content_type" type="text" placeholder="text/plain" class="form-control input-md">
                            </div>
                        </div>

                        <!-- Text input-->
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="timeout">Timeout before response</label>
                            <div class="col-md-4">
                                <input id="timeout" name="timeout" type="number" max="10" min="0" placeholder="0" value="0" class="form-control input-md">
                            </div>
                        </div>

                        <!-- Textarea -->
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="default_content">Response body</label>
                            <div class="col-md-7">
                                <textarea class="form-control" id="default_content" name="default_content" rows="5"></textarea>
                            </div>
                        </div>

                    </fieldset>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" ng-click="getCustomToken()">Create</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-5230636-9', 'auto');
    ga('send', 'pageview');
</script>
</div>
</body>
</html>
