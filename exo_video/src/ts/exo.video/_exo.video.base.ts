class ExoVideoBase extends ExoData implements ExoVideoProviderInterface {
  protected defaults: {
    position: 'absolute',
    zIndex: '-1',
    videoRatio: false,
    loop: true,
    autoplay: true,
    mute: false,
    mp4: false,
    webm: false,
    ogg: false,
    provider: 'image',
    videoId: null,
    image: false,
    when: 'always', // always || hover || viewport
    sizing: 'cover', // contain || cover
    start: 0,
    expand: false,
    expanded: false, // Should not be passed.
  };
  protected $wrapper:JQuery;
  protected $videoWrapper:JQuery;
  protected $expand:JQuery;
  protected $video:JQuery;
  protected $control:JQuery;
  protected player:any;
  protected ready:boolean = false;
  protected expanded:boolean = false;

  constructor(id:string, $wrapper:JQuery) {
    super(id);
    this.$wrapper = $wrapper;
  }

  public build(data):Promise<ExoSettingsGroupInterface> {
    return new Promise((resolve, reject) => {
      super.build(data).then(data => {
        if (data !== null) {
          this.setInnerWrapper();
          this.make();
        }
        resolve(data);
      }, reject);
    });
  }

  protected make() {
    this.$video = jQuery('<div id="' + this.getId() + '-video" class="exo-video-bg" style="transform: translate(-200vw, 0);"></div>').appendTo(this.$videoWrapper).css({
      position: this.get('sizing') === 'cover' ? 'absolute' : 'relative',
    });

    if (!this.get('expanded')) {
      this.$video.css({
        pointerEvents: 'none'
      });
    }

    if (this.get('controls')) {
      this.makeControls();
    }
  }

  protected makeControls() {
    if (typeof this.$control === 'undefined') {
      this.$control = jQuery('<div id="' + this.getId() + '-controls" class="exo-video-bg-control" style="display:none"></div>');
      var $toggle = jQuery('<div class="exo-video-bg-toggle" tabindex="0"></div>').on('click', e => {
        var $toggle = jQuery(e.target);
        this.toggle($toggle);
      }).on('keydown', e => {
        switch (e.which) {
          case 13: // enter
          case 32: // space
            e.preventDefault();
            e.stopPropagation();
            var $toggle = jQuery(e.target);
            this.toggle($toggle);
            break;
        }
      }).appendTo(this.$control);
      if (this.get('autoplay')) {
        $toggle.text('Pause').addClass('exo-video-bg-pause');
      }
      else {
        $toggle.text('Play').addClass('exo-video-bg-play');
      }
      this.$control.appendTo(this.$wrapper);
    }
  }

  protected toggle($toggle:JQuery):void {
    if ($toggle.hasClass('exo-video-bg-play')) {
      this.videoPlay();
      $toggle.text('Pause').removeClass('exo-video-bg-play').addClass('exo-video-bg-pause');
    }
    else {
      this.videoPause();
      $toggle.text('Play').removeClass('exo-video-bg-pause').addClass('exo-video-bg-play');
    }
  }

  protected setInnerWrapper():void {
    this.$videoWrapper = jQuery('<div class="exo-video-bg-wrapper"></div>').appendTo(this.$wrapper).css({
      zIndex: this.get('zIndex'),
      position: this.get('sizing') === 'cover' ? this.get('position') : 'relative',
      width: '100%'
    });
    if (this.get('sizing') === 'cover') {
      this.$videoWrapper.css({
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        overflow: 'hidden'
      });
    }
    else {
      this.$videoWrapper.css({
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
      });
    }
    this.makeImageBackground();

    if (this.get('expand')) {
      const $open = jQuery('<a class="exo-video-bg-expand-open"><span>Open</span></a>').on('click.exo.video', e => {
        if (Drupal.Exo.isMobile()) {
          $open.hide();
          this.videoRewind();
          this.videoUnMute();
          setTimeout(() => {
            this.$wrapper.on('click.exo.video', e => {
              this.$wrapper.off('click.exo.video');
              this.videoMute();
              $open.show();
            });
          });
        }
        else {
          this.videoExpand();
        }
      }).appendTo(this.$videoWrapper);
    }

    // Track element position to make sure video is resized correctly due to
    // images that control size that might not be loaded.
    Drupal.Exo.trackElementPosition(this.$wrapper, $element => {
      setTimeout(() => {
        this.videoResize();
      });
    });
  }

  protected makeImageBackground():void {
    if (this.get('image')) {
      var parameters = {
        backgroundImage: 'url(' + this.get('image') + ')',
        backgroundSize: this.get('sizing'),
        backgroundPosition: 'center center',
        backgroundRepeat: 'no-repeat',
      };
      this.$videoWrapper.css(parameters);
    }
  }

  public getWrapper():JQuery {
    return this.$wrapper;
  }

  protected videoReady() {
    Drupal.ExoVideo.onReady(this);
    this.ready = true;
    this.$video = jQuery('#' + this.getId() + '-video');

    if (this.get('mute')) {
      this.videoMute();
    }
    else {
      this.videoUnMute();
    }

    this.videoResizeBind();
  }

  protected videoResizeBind() {
    if (this.get('videoRatio') !== false) {
      Drupal.Exo.$window.on('resize.video-bg', {}, Drupal.debounce(e => {
        this.videoResize();
      }, 100));
      this.videoResize();
    }
  }

  protected videoResize() {
    this.$video.css({
      width: '',
    });
    var w = this.$videoWrapper.innerWidth();
    var h = this.$videoWrapper.innerHeight();
    if (h === 0) {
      h = w / this.get('videoRatio');
    }
    let parameters = {};

    var width = w;
    var height = h;
    if (this.get('sizing') === 'cover') {
      height = w / this.get('videoRatio');
      if (height < h) {
        height = h;
        width = h * this.get('videoRatio');
      }
    }
    else {
      parameters['position'] = 'relative';
      height = w / this.get('videoRatio');
    }

    // Round
    height = Math.ceil(height);
    width = Math.ceil(width);

    // Adjust
    if (this.get('sizing') === 'cover') {
      var top = Math.round(h / 2 - height / 2);
      var left = Math.round(w / 2 - width / 2);
      parameters['top'] = top + 'px';
      parameters['left'] = left + 'px';
    }

    parameters['width'] = width + 'px';
    parameters['height'] = height + 'px';

    this.$video.css(parameters);
  }

  protected videoWatch() {
    if (!this.get('autoplay')) {
      this.videoPause();
    }
    // if (this.get('expand')) {
    //   this.videoExpandBind();
    // }
    if (this.get('expanded')) {
      this.videoContractBind();
    }
    switch (this.get('when')) {
      case 'hover':
        this.videoPause();
        this.videoHoverBind();
        break;

      case 'viewport':
        this.videoViewportBind();
        break;
    }
    if (this.get('controls')) {
      this.$control.fadeIn();
    }
  }

  protected videoExpand() {
    var expandId = this.$wrapper.attr('id') + '-expand';
    this.$expand = this.$wrapper.clone();
    this.$expand.html('');
    this.$expand.attr('id', expandId);
    this.$expand.addClass('exo-video-bg-expand');
    jQuery('<a class="exo-video-bg-expand-close"><span>Close</span></a>').appendTo(this.$expand);
    this.$expand.appendTo(Drupal.Exo.$exoCanvas).css({
      position: 'fixed',
      top: 0,
      right: 0,
      bottom: 0,
      left: 0,
      zIndex: 9999
    });
    const data = jQuery.extend(true, {}, this.getData(), {
      mute: false,
      loop: false,
      when: 'always',
      expand: false,
      expanded: true,
      sizing: 'contain',
    });
    Drupal.ExoVideo.create(expandId, data);
    Drupal.Exo.lockOverflow(this.$expand);
  }

  protected videoContractBind() {
    this.$videoWrapper.add('.exo-video-bg-expand-close', this.$wrapper[0]).on('click.exo.video', (e) => {
      e.preventDefault();
      Drupal.Exo.unlockOverflow(this.$wrapper);
      this.$wrapper.remove();Drupal.ExoVideo.removeInstance(this.$wrapper.attr('id'));
    });
  }

  protected videoHoverBind() {
    this.$videoWrapper.on('mouseenter.exo.video', e => {
      this.videoPlay();
    }).on('mouseleave.exo.video', e => {
      this.videoPause();
    });
  }

  protected videoViewportBind() {
    const timeout = setTimeout(() => {
      this.videoPause();
    }, 10);
    Drupal.Exo.trackElementPosition(this.$videoWrapper, () => {
      clearTimeout(timeout);
      this.videoPlay();
    }, () => {
      clearTimeout(timeout);
      this.videoPause();
    });
  }

  public videoTime() { }

  protected videoPlay() {}

  protected videoPause() {}

  protected videoRewind() {}

  protected videoMute() {}

  protected videoUnMute() {}

}
