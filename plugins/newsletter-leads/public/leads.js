// GDPR: used only for technical cookies
function tnp_leads_set_cookie(name, value, time) {
    var e = new Date();
    e.setTime(e.getTime() + time * 24 * 60 * 60 * 1000);
    document.cookie = name + "=" + value + "; expires=" + e.toGMTString() + "; path=/";
}

function tnp_leads_get_cookie(name, def) {
    var cs = document.cookie.toString().split('; ');
    var c, n, v;
    for (var i = 0; i < cs.length; i++) {
        c = cs[i].split("=");
        n = c[0];
        v = c[1];
        if (n == name)
            return v;
    }
    return def;
}

function tnp_leads_open() {
    fetch(tnp_leads_url).then(data => {
        data.text().then(body => {
            var modal_body = document.getElementById('tnp-modal-body');
            modal_body.innerHTML = body;
            var modal = document.getElementById('tnp-modal');
            modal.style.display = 'block';
            //console.log(body);
        });
    }).catch(error => {
        console.error(error);
    });
}

// Close modal
function tnp_leads_close() {
    var modal = document.getElementById('tnp-modal');
    modal.style.display = 'none';
}

// Click outside and close
function tnp_outside_click(e) {
    var modal = document.getElementById('tnp-modal');
    if (e.target == modal) {
        modal.style.display = 'none';
    }
}

window.addEventListener('click', tnp_outside_click);
document.getElementById('tnp-modal-close').addEventListener('click', tnp_leads_close);

if (tnp_leads_test) {
    tnp_leads_open();
} else {
    if (tnp_leads_get_cookie("newsletter", null) == null) {
        var count = parseInt(tnp_leads_get_cookie("newsletter_leads", 0));
        tnp_leads_set_cookie("newsletter_leads", count + 1, tnp_leads_days);
        if (count == tnp_leads_count) {
            setTimeout(tnp_leads_open, tnp_leads_delay);
        } else {
            console.log('Count not matching');
        }
    }
}

function tnp_leads_submit(form) {
    const form_data = new FormData(form);
    fetch(tnp_leads_post, {
        method: 'POST',
        body: form_data
    }).then(data => {
        data.text().then(body => {
            document.getElementById('tnp-modal-html').innerHTML =
                    '<div class="tnp-success-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="120px" height="120px" viewBox="0 0 48 48"><g ><path fill="#fff" d="M22,45C10.4209,45,1,35.57959,1,24S10.4209,3,22,3c3.91211,0,7.72852,1.08301,11.03809,3.13184'
                    + 'c0.46973,0.29053,0.61426,0.90674,0.32422,1.37646c-0.29102,0.47021-0.90723,0.61426-1.37695,0.32373'
                    + 'C28.99219,5.97949,25.54004,5,22,5C11.52344,5,3,13.52344,3,24s8.52344,19,19,19s19-8.52344,19-19'
                    + 'c0-1.69238-0.22266-3.37207-0.66211-4.99268c-0.14453-0.5332,0.16992-1.08252,0.70312-1.22705'
                    + 'c0.53418-0.14209,1.08301,0.1709,1.22656,0.70361C42.75391,20.2749,43,22.13086,43,24C43,35.57959,33.5791,45,22,45z"/>'
                    + '<path fill="#72C472" d="M22,29c-0.25586,0-0.51172-0.09766-0.70703-0.29297l-8-8c-0.39062-0.39062-0.39062-1.02344,0-1.41406'
                    + 's1.02344-0.39062,1.41406,0L22,26.58594L43.29297,5.29297c0.39062-0.39062,1.02344-0.39062,1.41406,0s0.39062,1.02344,0,1.41406'
                    + 'l-22,22C22.51172,28.90234,22.25586,29,22,29z"/></g></svg></div>' + body;
        })
    });
}