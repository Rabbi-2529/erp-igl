var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/offline',
    '/asset/bower_components/bootstrap/dist/css/bootstrap.min.css',
    '/asset/bower_components/font-awesome/css/font-awesome.min.css',
    '/asset/css/richtext.min.css',
    '/asset/dist/css/AdminLTE.min.css',
    '/asset/dist/css/skins/_all-skins.min.css',
    '/asset/bower_components/morris.js/morris.css',
    '/asset/bower_components/jvectormap/jquery-jvectormap.css',
    '/asset/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css',
    '/asset/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css',
    '/asset/css/rowReorder.dataTables.min.css',
    '/asset/css/responsive.dataTables.min.css',
    '/asset/bower_components/select2/dist/css/select2.min.css',
    '/asset/bower_components/bootstrap-daterangepicker/daterangepicker.css',
    '/asset/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css',
    '/asset/bower_components/jquery/dist/jquery.min.js',
    '/asset/bower_components/jquery-knob/dist/jquery.knob.min.js',
    '/asset/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js',
    '/asset/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js',
    '/asset/bower_components/moment/min/moment.min.js',
    '/asset/bower_components/bootstrap-daterangepicker/daterangepicker.js',
    '/asset/bower_components/datatables.net/js/jquery.dataTables.min.js',
    '/asset/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js',
    '/asset/dist/js/adminlte.min.js',
    '/asset/bower_components/select2/dist/js/select2.min.js',
    '/images/icons/icon-72x72.png',
    '/images/icons/icon-96x96.png',
    '/images/icons/icon-128x128.png',
    '/images/icons/icon-144x144.png',
    '/images/icons/icon-152x152.png',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-384x384.png',
    '/images/icons/icon-512x512.png',
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa1-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});
