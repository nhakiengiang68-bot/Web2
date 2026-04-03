      </div>
      <script src="js/jquery.min.js"></script>
      <script src="js/popper.min.js"></script>
      <script src="js/bootstrap.min.js"></script>
      <script src="js/select2.min.js"></script>
      <script src="js/jquery.magnific-popup.min.js"></script>
      <script src="js/custom.js"></script>
      <script>
        (function () {
          const btn = document.querySelector('.wrapper-menu');
          const syncMenuState = function () {
            if (!btn) return;
            btn.classList.toggle('open', document.body.classList.contains('sidebar-main'));
          };

          syncMenuState();
          window.addEventListener('resize', syncMenuState);

          const searchForm = document.querySelector('.iq-search-bar form');
          if (searchForm) {
            searchForm.addEventListener('submit', function (e) {
              const input = searchForm.querySelector('input[name="q"]');
              if (input && !input.value.trim()) {
                e.preventDefault();
              }
            });
          }
        })();
      </script>
   </body>
</html>
