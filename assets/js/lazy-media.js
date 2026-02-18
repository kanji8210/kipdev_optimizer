(function(){
    'use strict';

    function swapSrc(el) {
        if (!el) return;
        // iframe
        if (el.tagName === 'IFRAME') {
            var data = el.getAttribute('data-src');
            if (data) {
                el.setAttribute('src', data);
                el.removeAttribute('data-src');
            }
            return;
        }

        // video or audio
        if (el.tagName === 'VIDEO' || el.tagName === 'AUDIO') {
            // set sources from children <source data-src>
            var sources = el.querySelectorAll('source[data-src]');
            if (sources.length) {
                sources.forEach(function(s){
                    s.src = s.getAttribute('data-src');
                    s.removeAttribute('data-src');
                });
                try { el.load(); } catch (e) {}
            } else {
                var data = el.getAttribute('data-src');
                if (data) {
                    el.setAttribute('src', data);
                    el.removeAttribute('data-src');
                    try { el.load(); } catch (e) {}
                }
            }
            return;
        }
    }

    function prepareDeferredMedia() {
        // Defer iframes that have data-src already (from plugin)
        var iframes = document.querySelectorAll('iframe.kipdev-lazy-iframe[data-src]');

        // Defer videos and audios: move immediate src to data-src and set preload none
        var media = document.querySelectorAll('video, audio');
        media.forEach(function(el){
            // skip if already prepared
            if (el.hasAttribute('data-src') || el.getAttribute('data-kipdev-prepared')) return;

            // handle <source> children
            var sources = el.querySelectorAll('source[src]');
            if (sources.length) {
                sources.forEach(function(s){
                    s.setAttribute('data-src', s.getAttribute('src'));
                    s.removeAttribute('src');
                });
            } else if (el.hasAttribute('src')) {
                el.setAttribute('data-src', el.getAttribute('src'));
                el.removeAttribute('src');
            }

            // set preload to none to avoid fetching
            try { el.setAttribute('preload', 'none'); } catch (e) {}
            el.setAttribute('data-kipdev-prepared', '1');
        });

        // Combine targets
        var targets = Array.prototype.slice.call(iframes).concat(Array.prototype.slice.call(media));
        return targets;
    }

    function onIntersect(entries, observer) {
        entries.forEach(function(entry){
            if (entry.isIntersecting) {
                var el = entry.target;
                swapSrc(el);
                observer.unobserve(el);
            }
        });
    }

    function init() {
        if (!('IntersectionObserver' in window)) {
            // Fallback: load all
            document.querySelectorAll('iframe.kipdev-lazy-iframe[data-src]').forEach(function(i){
                swapSrc(i);
            });
            document.querySelectorAll('video[data-src], audio[data-src]').forEach(function(m){
                swapSrc(m);
            });
            return;
        }

        var targets = prepareDeferredMedia();
        if (!targets || !targets.length) return;

        var observer = new IntersectionObserver(onIntersect, {
            root: null,
            rootMargin: '200px',
            threshold: 0.01
        });

        targets.forEach(function(t){ observer.observe(t); });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
