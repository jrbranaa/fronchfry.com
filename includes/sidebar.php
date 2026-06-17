<aside class="sidebar">
    <iframe id="yajigo-widget" 
        src="https://yajigo.com/widget"
        scrolling="no" 
        frameborder="0" 
        style="width:100%;border:none;display:block;">
    </iframe>
</aside>

<script>
window.addEventListener('message', function(e) {
  if (e.data && e.data.type === 'yajigo-resize') {
    document.getElementById('yajigo-widget').style.height = e.data.height + 'px';
  }
});
</script>
