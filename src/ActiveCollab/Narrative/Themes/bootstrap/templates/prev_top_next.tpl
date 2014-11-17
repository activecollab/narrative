<div class="text-center">
  <ul class="pagination">
    <li class="prev"><a href="#">&laquo;</a></li>
    <li class="top"><a href="#" onclick="window.scrollTo(0, 0); return false;">Top</a></li>
    <li class="next"><a href="#">&raquo;</a></li>
  </ul>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    var prev_link = $('ul.pagination li.prev a');
    var next_link = $('ul.pagination li.next a');

    /**
     * Hide both links
     */
    var hide_both = function() {
      if (prev_link.length) {
        prev_link.parent().hide();
      }

      if (next_link.length) {
        next_link.parent().hide();
      }
    }

    /**
     * Hide prev link
     */
    var hide_prev = function() {
      if (prev_link.length) {
        prev_link.parent().hide();
      }
    }

    /**
     * Hide next link
     */
    var hide_next = function() {
      if (next_link.length) {
        next_link.parent().hide();
      }
    }

    var sidebar = $('#sidebar');

    if (sidebar.length) {
      var prev = null, next = null, prev_item, finish_with_next = false;

      sidebar.find('li').each(function() {
        var item = $(this);

        if (finish_with_next) {
          next = item;
          return false; // and break
        }

        if (item.is('.selected')) {
          prev = prev_item;
          finish_with_next = true;
        }

        prev_item = item; // Remember for the next iteration
      });

      if (prev && prev.length) {
        prev_link.attr('href', prev.find('a').attr('href'));
      } else {
        hide_prev();
      }

      if (next && next.length) {
        next_link.attr('href', next.find('a').attr('href'));
      } else {
        hide_next();
      }
    } else {
      hide_both();
    }
  });
</script>