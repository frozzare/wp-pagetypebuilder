import $ from 'jquery';
import Utils from 'papi/utils';

/**
 * Property File.
 *
 * Using the build in media management in WordPress.
 */

class File {

  /**
   * The image template to compile.
   *
   * @return {function}
   */

  get template() {
    return window.wp.template('papi-property-file');
  }

  /**
   * Initialize Property Image.
   */

  static init() {
    new File().binds();
  }

  /**
   * Bind elements with functions.
   */

  binds() {
    $('.inside .papi-table > tbody .papi-property-file.multiple .attachments').sortable({
      revert: true
    });

    const self = this;
    $(document).on('click', '.papi-property-file .papi-file-select > .button', function (e) {
      e.preventDefault();
      self.add($(this));
    });

    $(document).on('mouseenter mouseleave', '.papi-property-file .attachment', this.hover);
    $(document).on('click', '.papi-property-file .attachment a', this.remove);
    $(document).on('change', '.papi-property-repeater-top', this.update);
    $(document).on('click', '.papi-property-file .attachment', this.replace);
  }

  /**
   * Add new image.
   *
   * @param {object} e
   */

  add($this) {
    const $prop    = $this.closest('.papi-property-file');
    const $select  = $this.closest('p');
    const $target  = $prop.find('.attachments');
    const fileType = $prop.data('file-type');
    const multiple = $prop.hasClass('multiple');
    const slug     = $this.data().slug;
    const self     = this;
    let   library  = {};

    if (fileType === 'image') {
      library.type = 'image';
    }

    Utils.wpMediaEditor({
      library: library,
      multiple: multiple
    }).on('insert', (attachment) => {
      let data = {
        alt:  attachment.alt,
        id:   attachment.id,
        slug: slug,
        url:  attachment.url
      };

      if (attachment.sizes !== undefined && attachment.sizes.thumbnail !== undefined) {
        data.url = attachment.sizes.thumbnail.url;
      }

      if (attachment.type !== 'image') {
        data.url = attachment.icon;
      }

      if (fileType === 'file') {
        data.filename = attachment.filename;
      }

      self.render($target, data);

      if (!multiple) {
        $select.addClass('papi-hide');
      }
    }).open();
  }

  /**
   * Toggle the remove button.
   *
   * @param {object} e
   */

  hover(e) {
    e.preventDefault();

    $(this).find('a').toggle();
  }

  /**
   * Remove a image.
   *
   * @param {object} e
   */

  remove(e) {
    e.stopPropagation();
    e.preventDefault();

    const $this = $(this);

    $this.closest('.papi-property-file')
      .find('.papi-file-select')
      .removeClass('papi-hide');

    $this.closest('.attachment')
      .remove();
  }

  /**
   * Render the image with the template.
   *
   * @param {object} $el
   * @param {object} data
   */

  render($el, data) {
    let template = this.template;
    template = window._.template(template());
    $el.append('<div class="attachment">' + template(data) + '</div>');
  }

  /**
   * Replace image with another one.
   *
   * @param {object} e
   */

  replace(e) {
    e.preventDefault();

    const $this   = $(this);
    const $prop   = $this.closest('.papi-property-file');
    const $img    = $this.find('img[src]');
    const $input  = $this.find('input[type=hidden]');
    const postId  = $input.val();
    let   library = {};

    if ($prop.data('file-type') === 'image') {
      library.type = 'image';
    }

    Utils.wpMediaEditor({
      library: library,
      multiple: false
    }).on('open', () => {
      let   selection = Utils.wpMediaFrame.state().get('selection');
      const attachment = window.wp.media.attachment(postId);

      attachment.fetch();
      selection.add(attachment ? [attachment] : []);
    }).on('insert', (attachment) => {
      let url = attachment.url;

      if (attachment.sizes !== undefined && attachment.sizes.thumbnail !== undefined) {
        url = attachment.sizes.thumbnail.url;
      }

      if (attachment.type !== 'image') {
        url = attachment.icon;
      }

      if ($prop.data('file-type') === 'file') {
        $this.find('.filename div').text(attachment.filename);
      }

      $img.attr('src', url).attr('alt', attachment.alt);
      $input.val(attachment.id);
    }).open();
  }

  /**
   * Update when added to repeater.
   */

  update(e) {
    e.preventDefault();

    $(this)
      .find('tr').last()
      .find('.attachments')
      .sortable({
        revert: true
      });
  }

}

export default File;
