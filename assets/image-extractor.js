(function () {
  'use strict';

  var root = document.querySelector('[data-ufxie-extractor]');
  if (!root || !window.UFXIE) return;

  var field = root.querySelector('#ufxie-page-url');
  var button = root.querySelector('.ufxie-run');
  var status = root.querySelector('.ufxie-status');
  var output = root.querySelector('.ufxie-output');

  function track(eventName, state) {
    if (window.ufxTrack) window.ufxTrack(eventName, { tool_slug: 'website-image-extractor', result_state: state || '' });
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>'"]/g, function (character) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[character];
    });
  }

  function setStatus(message, state) {
    status.textContent = message || '';
    status.className = 'at-help ufxie-status' + (state ? ' is-' + state : '');
  }

  function post(action, data, retried) {
    var body = new URLSearchParams();
    body.append('action', action);
    Object.keys(data || {}).forEach(function (key) { body.append(key, data[key]); });
    var controller = window.AbortController ? new AbortController() : null;
    var timer = controller ? setTimeout(function () { controller.abort(); }, 55000) : null;

    return fetch(UFXIE.ajax, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body: body.toString(),
      signal: controller ? controller.signal : undefined
    }).then(function (response) {
      if (timer) clearTimeout(timer);
      return response.json().catch(function () { throw new Error('The server returned an invalid response.'); });
    }, function (error) {
      if (timer) clearTimeout(timer);
      if (error && error.name === 'AbortError') throw new Error('The webpage took too long to inspect. Please try again.');
      throw error;
    }).then(function (json) {
      if (json && json.success) return json.data;
      var message = json && json.data && json.data.message ? json.data.message : 'Image extraction failed.';
      if (!retried && message === 'Invalid request.') {
        return post('ufxie_refresh_nonce', {}, true).then(function (fresh) {
          if (!fresh || !fresh.nonce) throw new Error(message);
          UFXIE.nonce = fresh.nonce;
          data.nonce = fresh.nonce;
          return post(action, data, true);
        });
      }
      throw new Error(message);
    });
  }

  function copyText(value) {
    if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(value);
    return new Promise(function (resolve, reject) {
      var area = document.createElement('textarea');
      area.value = value;
      area.style.position = 'fixed';
      area.style.opacity = '0';
      document.body.appendChild(area);
      area.select();
      try { document.execCommand('copy'); resolve(); } catch (error) { reject(error); }
      document.body.removeChild(area);
    });
  }

  function downloadCsv(images) {
    function quote(value) { return '"' + String(value == null ? '' : value).replace(/"/g, '""') + '"'; }
    var rows = [['URL', 'Source', 'Alt text', 'Declared dimensions']].concat(images.map(function (image) {
      return [image.url, image.source, image.alt, image.dimensions];
    }));
    var blob = new Blob([rows.map(function (row) { return row.map(quote).join(','); }).join('\r\n')], { type: 'text/csv;charset=utf-8' });
    var objectUrl = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = objectUrl;
    link.download = 'website-images.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    setTimeout(function () { URL.revokeObjectURL(objectUrl); }, 1000);
  }

  function render(data) {
    var metrics = Array.isArray(data.results) ? data.results : [];
    var images = Array.isArray(data.images) ? data.images : [];
    var html = '<div class="ufxie-metrics">' + metrics.map(function (item) {
      return '<div class="ufxie-metric"><span>' + escapeHtml(item.label) + '</span><strong>' + escapeHtml(item.value) + '</strong></div>';
    }).join('') + '</div>';

    if (images.length) {
      html += '<div class="ufxie-toolbar"><strong>' + images.length + ' unique images</strong><div><button type="button" class="at-btn at-btn-outline ufxie-copy-all">Copy All URLs</button><button type="button" class="at-btn at-btn-primary ufxie-csv">Download CSV</button></div></div>';
      html += '<div class="ufxie-gallery">' + images.map(function (image, index) {
        var alt = image.alt || 'No alt text';
        return '<article class="ufxie-image-card"><a class="ufxie-preview" href="' + escapeHtml(image.url) + '" target="_blank" rel="noopener noreferrer nofollow"><img src="' + escapeHtml(image.url) + '" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer"><span>Open original</span></a><div class="ufxie-image-body"><strong>Image ' + (index + 1) + '</strong><small>' + escapeHtml(image.source || 'Page image') + ' · ' + escapeHtml(image.dimensions || 'Not declared') + '</small><p title="' + escapeHtml(alt) + '">' + escapeHtml(alt) + '</p><div><a class="at-btn at-btn-outline" href="' + escapeHtml(image.url) + '" target="_blank" rel="noopener noreferrer nofollow">Open / Save</a><button type="button" class="at-btn at-btn-outline ufxie-copy-one" data-url="' + escapeHtml(image.url) + '">Copy URL</button></div></div></article>';
      }).join('') + '</div>';
    } else {
      html += '<div class="ufxie-empty">No public image references were found in the fetched webpage HTML.</div>';
    }
    if (data.notice) html += '<p class="at-help ufxie-notice">' + escapeHtml(data.notice) + '</p>';
    output.innerHTML = html;

    output.querySelectorAll('.ufxie-copy-one').forEach(function (copyButton) {
      copyButton.addEventListener('click', function () {
        copyText(copyButton.getAttribute('data-url') || '').then(function () {
          copyButton.textContent = 'Copied';
          track('tool_copy', 'success');
          setTimeout(function () { copyButton.textContent = 'Copy URL'; }, 1400);
        });
      });
    });
    var copyAll = output.querySelector('.ufxie-copy-all');
    if (copyAll) copyAll.addEventListener('click', function () {
      copyText(images.map(function (image) { return image.url; }).join('\n')).then(function () {
        copyAll.textContent = 'URLs Copied';
        track('tool_copy', 'success');
        setTimeout(function () { copyAll.textContent = 'Copy All URLs'; }, 1500);
      });
    });
    var csv = output.querySelector('.ufxie-csv');
    if (csv) csv.addEventListener('click', function () { downloadCsv(images); track('tool_download', 'success'); });
    output.querySelectorAll('.ufxie-preview img').forEach(function (image) {
      image.addEventListener('error', function () { image.closest('.ufxie-preview').classList.add('is-unavailable'); });
    });
  }

  function run() {
    var url = String(field.value || '').trim();
    if (!url) {
      setStatus('Enter a public webpage URL.', 'error');
      field.focus();
      return;
    }
    button.disabled = true;
    output.innerHTML = '';
    setStatus('Fetching the webpage and extracting image references…', 'working');
    track('tool_run', 'started');
    post('ufxie_extract_images', { url: url, nonce: UFXIE.nonce }, false).then(function (data) {
      render(data);
      setStatus('Image extraction completed.', 'success');
      track('tool_result', 'success');
    }).catch(function (error) {
      setStatus(error.message || 'Image extraction failed.', 'error');
      track('tool_error', 'error');
    }).then(function () { button.disabled = false; });
  }

  button.addEventListener('click', run);
  field.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') { event.preventDefault(); run(); }
  });
})();
