<footer class="app-footer">
    <div class="site-footer-right font-weight-bolder">
     {{date('Y')}} - <code>v{{trim(file_get_contents(public_path('/version')))}}</code>&nbsp; <span class="label label-success">Active</span>
    </div>
</footer>
