(function ($) {
  $(document).ready(function () {
    //Handlers for forms
    $('input, textarea, select').on('focusout', function () {
      $('.form-item').removeClass('group-error');
      $('.form-item').children('.group-error-description').remove();
      $('.alert-danger-list').hide();
      $('.error-list').html('');

      validateElement(this);
    });

    $('.btn-submit').on('click', function () {
      $('.form-item').removeClass('group-error');
      $('.form-item').children('.group-error-description').remove();
      $('.alert-danger-list').hide();
      $('.error-list').html('');

      validateForm(this);
    });

    //Textarea word count
    $(".summary-word-count").html($("textarea").val().length);

    //Textarea red text after maxlength
    $("textarea").keyup(function () {
      var maxlength = 150; // specify the maximum length
      $(".summary-word-count").html($("textarea").val().length);

      if ($("textarea").val().length > maxlength) {
        $("textarea").css('color', 'red');
      } else {
        $("textarea").css('color', 'black');
      }
    });
  });

  function validateElement(elementP) {
    var form = $("#test-form");
    form.validate();

    //Adds errors to input that are empty.
    if ($(elementP).val().length == 0 && $(elementP).attr('required') == 'required') {
      $(elementP).addClass('error'); //add check for required
    }

    //Selects all invalid fields and adds parent
    $('.error').each(function () {
      var message = 'The <strong>' + $(this).attr('drupal-field-name') + '</strong> field is required.'
      $(this).closest('.form-item').addClass('group-error');
      $(this).closest('.form-item').append('<p class="group-error-description">' + message + '</p>');
    });

    //Add additional validation
    if ($('textarea').val().length > 150) {
      $('textarea').addClass('error');
      $('textarea').closest('.form-item').addClass('group-error');
      var message = 'The <strong>' + $('textarea').attr('drupal-field-name') + '</strong> is over the character limit.'
      $('textarea').closest('.form-item').append('<p class="group-error-description">' + message + '</p>')
    }

    //Check for valid email address
    if ($('#edit-primary-contact-email').val().length > 1 && !validateEmail($('#edit-primary-contact-email').val())) {
      $('#edit-primary-contact-email').addClass('error');
      $('#edit-primary-contact-email').closest('.form-item').addClass('group-error');
      var message = 'The <strong>' + $('#edit-primary-contact-email').attr('drupal-field-name') + '</strong> is not a valid email address.'
      $('#edit-primary-contact-email').closest('.form-item').append('<p class="group-error-description">' + message + '</p>')
    }

    //Add messages to alert above. Show is error count is over 1
    if ($('.group-error-description').length > 0) {
      $('.alert-danger-list').show();
      $('.group-error-description').each(function () {
        $('.error-list').append('<li><a href="#' + $(this).parent().children('input').attr("id") + '">' + $(this).html() + '</a></li>');
      });
    }
  }

  function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
  }

  function validateForm() {
    var form = $("#test-form");
    form.validate();

    //Selects all invalid fields and adds parent
    setTimeout(function () {
      $('.error').each(function () {
        var message = 'The <strong>' + $(this).attr('drupal-field-name') + '</strong> field is required.'
        $(this).closest('.form-item').addClass('group-error');
        $(this).closest('.form-item').append('<p class="group-error-description">' + message + '</p>')
      });

      //Add additional validation
      if ($('textarea').val().length > 150) {
        $('textarea').addClass('error');
        var message = 'The <strong>' + $('textarea').attr('drupal-field-name') + '</strong> is over the character limit.'
        $('textarea').closest('.form-item').append('<p class="group-error-description">' + message + '</p>')
      }

      //Check for valid email address
      if ($('#edit-primary-contact-email').val().length > 1 && !validateEmail($('#edit-primary-contact-email').val())) {
        $('#edit-primary-contact-email').addClass('error');
        var message = 'The <strong>' + $('#edit-primary-contact-email').attr('drupal-field-name') + '</strong> is not a valid email address.'
        $('#edit-primary-contact-email').closest('.form-item').append('<p class="group-error-description">' + message + '</p>')
      }

      //Add messages to alert above. Show is error count is over 1
      if ($('.group-error-description').length > 0) {
        $('.alert-danger-list').show();
        $('.group-error-description').each(function () {
          $('.error-list').append('<li><a href="#' + $(this).parent().children('input').attr("id") + '">' + $(this).html() + '</a></li>');
        });
      }
    });

  }
})(jQuery);
