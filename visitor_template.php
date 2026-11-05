<?php
/**
 * Template by          : Tutorial SLiMS
 * Template description : Integrated library catalog search theme for SLiMS 9.8.0 (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Licensed under GPLv3.
 */

/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2020-01-03 08:49
 * @File name           : visitor_template.php
 */

$main_template_path = __DIR__ . '/login_template.inc.php';

// set default language
if (isset($_GET['select_lang'])) {
    $select_lang = trim(strip_tags($_GET['select_lang']));
    // delete previous language cookie
    if (isset($_COOKIE['select_lang'])) {
        #@setcookie('select_lang', $select_lang, time()-14400, SWB);
        #@setcookie('select_lang', $select_lang, time()-14400, SWB, "", FALSE, TRUE);

        @setcookie('select_lang', $select_lang, [
            'expires' => time()-14400,
            'path' => SWB,
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);



    }
    // create language cookie
    #@setcookie('select_lang', $select_lang, time()+14400, SWB);
    #@setcookie('select_lang', $select_lang, time()+14400, SWB, "", FALSE, TRUE);

    @setcookie('select_lang', $select_lang, [
        'expires' => time()+14400,
        'path' => SWB,
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);



    $sysconf['default_lang'] = $select_lang;
} else if (isset($_COOKIE['select_lang'])) {
    $sysconf['default_lang'] = trim(strip_tags($_COOKIE['select_lang']));
}

?>
<div class="kdv" id="visitor-counter">
    <i class="fas fa-star kd-deco star1" aria-hidden="true"></i>
    <i class="fas fa-star kd-deco star2" aria-hidden="true"></i>
    <span class="kd-deco dot" aria-hidden="true"></span>
    <span class="kd-deco ring" aria-hidden="true"></span>
    <span class="kd-deco dots" aria-hidden="true"></span>

    <button type="button" class="kd-icon-btn kdv-theme" id="kdv-theme-toggle" aria-label="<?= __('Toggle theme') ?>">
        <i class="fas fa-moon"></i>
    </button>

    <div class="kdv-wrap">
        <div class="kdv-card kdv-form-card">
            <a class="kd-brand kdv-brand" href="index.php">
                <?php
                // this file is included before login_template.inc.php / classic.php run,
                // so $imagesDisk and assets() are not available yet
                $kdvImagesDisk = \SLiMS\Filesystems\Storage::images();
                if (isset($sysconf['logo_image']) && $sysconf['logo_image'] != '' && $kdvImagesDisk->isExists($path = 'default/' . $sysconf['logo_image'])) {
                    echo '<img src="' . SWB . 'lib/minigalnano/createthumb.php?filename=images/' . $path . '&width=350" alt="logo">';
                }
                ?>
                <span class="kd-brand-text">
                    <strong><?= htmlspecialchars($sysconf['library_name']); ?></strong>
                    <?php if (!empty($sysconf['library_subname'])): ?>
                        <span><?= htmlspecialchars($sysconf['library_subname']); ?></span>
                    <?php endif; ?>
                </span>
            </a>

            <span class="kd-chip"><i class="fas fa-door-open"></i> <?= __('Visitor Counter') ?></span>
            <h1 class="kdv-title"><?= __('Welcome to ') ?><span><?= htmlspecialchars($sysconf['library_name']); ?></span></h1>
            <p class="kdv-lead"><?= __('Please fill your member ID or name.') ?></p>

            <form class="kdv-form" @submit.prevent="onSubmit">
                <label for="kdv-member"><?= __('Member ID') ?></label>
                <div class="kdv-field">
                    <i class="fas fa-id-card"></i>
                    <input v-model="memberId" ref="memberId" autofocus autocomplete="off" type="text" id="kdv-member"
                           placeholder="<?= __('Enter your member ID') ?>">
                </div>

                <label for="kdv-institution"><?= __('Institution') ?></label>
                <div class="kdv-field">
                    <i class="fas fa-building"></i>
                    <input v-model="institution" autocomplete="off" type="text" id="kdv-institution"
                           placeholder="<?= __('Enter your institution') ?>">
                </div>
                <small class="kdv-hint"><?= __('Enough fill your member ID if you are member of ') . htmlspecialchars($sysconf['library_name']); ?></small>

                <button type="submit" class="kdv-submit"><i class="fas fa-check-circle"></i> <?= __('Check In') ?></button>
            </form>

            <div class="kdv-powered"><?= __('Powered by ') ?> <code>SLiMS</code> &middot; Pustaka Ceria</div>
        </div>

        <div class="kdv-card kdv-side-card">
            <div v-show="textInfo !== ''" class="kdv-result">
                <div class="kdv-avatar">
                    <img :src="image" alt="image" @error="onImageError">
                </div>
                <h3 class="kdv-message" v-html="textInfo"></h3>
            </div>
            <blockquote class="kdv-quote" v-show="textInfo === ''">
                <i class="fas fa-quote-left"></i>
                <p>{{quotes.content}}</p>
                <footer>{{quotes.author}}</footer>
            </blockquote>
        </div>
    </div>
</div>

<script src="<?php echo $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/assets/js/axios.min.js'; ?>"></script>
<script src="<?= JWB . 'he.js' ?>"></script>
<script>
    new Vue({
        el: '#visitor-counter',
        data() {
            return {
                memberId: '',
                institution: '',
                textInfo: '',
                image: './images/persons/photo.png',
                quotes: {},
                timeout: null,
                csrfName: '<?= \Volnix\CSRF\CSRF::getTokenName() ?>',
                csrfToken: '<?= \Volnix\CSRF\CSRF::getToken() ?>'
            }
        },
        mounted() {
            this.$refs.memberId.focus()
            this.getQuotes()
        },
        methods: {
            onImageError: function() {
                this.image = './images/persons/photo.png'
            },
            getQuotes: function() {
                // Alternative Free Quotes API: https://api.quotable.io/random
                axios.get('https://slims.web.id/kutipan/')
                    .then(res => {
                        res.data.content = he.decode(res.data.content)
                        this.quotes = res.data
                    })
                    .catch(() => {
                        this.quotes = {
                            content: "Sing penting madhiang.",
                            author: "Pai-Jo"
                        }
                    })
                    .finally(() => {
                        this.textInfo = ''
                    })
            },
            onSubmit: function() {
                if (this.memberId === '') {
                    this.resetForm()
                    return
                }
                let url = 'index.php?p=visitor<?= trim(isset($_GET['room']) ? '&room=' . simbio_security::xssFree($_GET['room']) : '')  ?>'
                let data = new FormData()
                data.append('memberID', this.memberId)
                data.append('institution', this.institution)
                data.append('counter', 1)
                data.append(this.csrfName, this.csrfToken)

                axios({
                    url: url,
                    method: 'post',
                    data: data,
                    headers: {'Content-Type': 'multipart/form-data', 'X-Response-With': 'application/json' }
                })
                    .then(res => {
                        this.textInfo = res.data.message
                        this.image = `./images/persons/${res.data.image}`
                        if (res.data.new_token) {
                            this.csrfToken = res.data.new_token
                        }
                        <?php if ($sysconf['template']['visitor_log_voice']) : ?>
                            this.textToSpeech(this.textInfo.replace(/(<([^>]+)>)/ig, ''))
                        <?php endif; ?>
                    })
                    .catch(err => {
                        console.log(err);
                        if (err.response && err.response.data.new_token) {
                            this.csrfToken = err.response.data.new_token
                        }
                    })
                    .finally(() => {
                        this.resetForm()
                        clearTimeout(this.timeout)
                        this.timeout = setTimeout(() => {
                            this.getQuotes()
                        }, 5000)
                    })
            },
            resetForm: function () {
                this.memberId = ''
                this.institution = ''
                this.$refs.memberId.focus()
            },
            textToSpeech: function(message) {
                var message = new SpeechSynthesisUtterance(message);
                var voices = speechSynthesis.getVoices();
                // console.log(message);
                message['volume'] = 1;
                message['rate'] = 1;
                message['pitch'] = 1;
                message['lang'] = '<?php echo str_replace('_', '-', $sysconf['default_lang']); ?>';
                message['voice'] = null;
                speechSynthesis.cancel();
                speechSynthesis.speak(message);
            }
        }
    })

    // light / dark toggle (shares the 'kd-theme' setting with the rest of the theme)
    (function () {
        var btn = document.getElementById('kdv-theme-toggle');
        if (!btn) return;
        var root = document.documentElement;
        function paint() { btn.innerHTML = '<i class="fas ' + (root.getAttribute('data-theme') === 'dark' ? 'fa-sun' : 'fa-moon') + '"></i>'; }
        paint();
        btn.addEventListener('click', function () {
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('kd-theme', next); } catch (e) {}
            paint();
        });
    })();
</script>
