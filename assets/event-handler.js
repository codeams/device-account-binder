$(function() {

  var studentId = 'A14016371';//'A14016287';
  var studentName;

  $.ajaxSetup({
    dataType: 'JSON',
    type: 'post',
    url: 'control/ComunicationHandler.php',
    beforeSend: function() {
      $('#ui-locker').show();
      $('input:text').attr('disabled','disabled');
    },
    complete: function( response ) {
      console.log( response );
      $('#ui-locker').hide();
      $('input:text').removeAttr('disabled');
    }
  });

  // incorrect_user, ldap_error, invalid_user, name

  var viewTrasition = function( view1, view2, animationDuration ) {

    animationDuration = animationDuration || 1000;

    $( view1 ).fadeOut(animationDuration, function() {
      $( view2 ).fadeIn( animationDuration );
    });

  };

  var showDevices = function() {

    $.ajax({
      data: {
        'method' : 'getUserDevices',
        'matricula' : studentId
      },
      success: function( response ) {

       var devices = response.response;

       $.each(devices, function( index, device ) {
         device.id = device.idDispositivo;
         delete device.idDispositivo;
       });

       for ( var i = devices.length; i < 3; i++ ) {
         devices[i] = { 'id' : '0', 'nombre' : '', 'mac' : '' };
       }

        var devicesHtml = '';

        $.each(devices, function( index, device ) {

          devicesHtml +=
            "<div class='device "+ device.id +"' id='"+ device.id +"'>" +
              "<div class='options'>" +
                "<div class='option-icon delete'></div>" +
                "<div class='option-icon save'></div>" +
              "</div>" +
              "<div class='error-message'>Error al realizar la acción.</div>" +
              "<input type='text' name='name' autocomplete='off' spellcheck='false' class='name' value='"+ device.nombre +"'>" +
              "<input type='text' name='mac' autocomplete='off' spellcheck='false' class='mac' value='"+ device.mac +"'>" +
            "</div>";
        });

        $( '.devices' ).html( devicesHtml );
      },
      error: function() {
        alert( 'Error al obtener la lista de dispositivos.' );
        window.location.href = '';
      }
    });

  };


  $( '.login-form input:button' ).on('click', function() {

    var user = $('#user-input').val().toUpperCase();
    var password = $('#password-input').val();

    if ( user === '' || password === '' ) return;

    $.ajax({
      data: {
        'method' : 'authentication',
        'user' : user,
        'password' : password
      },
      success: function( response ) {

        var status = response.response;

        if ( status === 'ldap_error' ) {
          $( '.login-form .error-message' ).text( 'Ha ocurrido un error con el directorio de usuarios.' ).show();
        } else if ( status === 'invalid_user' ) {
          $( '.login-form .error-message' ).text( 'Este usuario no pertenece a la facultad de matemáticas.' ).show();
        } else if ( status === 'incorrect_user' ) {
          $( '.login-form .error-message' ).text( 'Usuario o contraseña incorrectos.' ).show();
        } else {
          studentId = user;
          studentName = response.response;
          $( '.login-form' ).css( 'display', 'none' );
          $( '.logout-link' ).css( 'display', 'block' );
          $( '.devices' ).css( 'display', 'block' );
          showDevices();
        }

      },
      error: function() {
        $( '.login-form .error-message' ).text( 'Ha ocurrido un error de comunicación.' ).show();
      }
    });

  });


  $( 'body' )
    .on('click', '.save', function( event ) {

      var device = $( this ).parents( '.device' )[0];
      var deviceId = $( device ).attr( 'id' );
      var deviceName = $( device ).find( 'input[name="name"]' ).val();
      var deviceMac = $( device ).find( 'input[name="mac"]' ).val().toUpperCase();

      if ( deviceName === '' ) {
        $( device ).find( '.error-message' ).text( 'Debes ingresar un nombre de dispositivo.' ).show();
        return;
      }

      // console.log( deviceId );
      // console.log( deviceName );

      if ( ! /^([0-9A-F]{2}[:]){5}([0-9A-F]{2})$/.test( deviceMac ) ) {
        $( device ).find( '.error-message' ).text( 'La dirección MAC no es válida.' ).show();
        return;
      }

      var data;

      if ( deviceId === '0' ) {
        data = {
          'method' : 'addDevice',
          'matricula' : studentId,
          'alias' : deviceName,
          'mac' : deviceMac
        };
      } else {
        data = {
          'method' : 'updateDevice',
          'id' : deviceId,
          'alias' : deviceName,
          'mac' : deviceMac
        };
      }

      $.ajax({
        data: data,
        success: function( response ) {
          if ( response.response ) {
            showDevices();
          } else {
            $( device ).find( '.error-message' ).text( 'No se ha podido actualizar la información.' ).show();
          }
        },
        error: function() {
          $( device ).find( '.error-message' ).text( 'Ha ocurrido un error de comunicación.' ).show();
        }
      });

    }).on('click', '.delete', function( event ) {

      var device = $( this ).parents( '.device' )[0];
      var deviceId = $( device ).attr( 'id' );

      if ( deviceId === '0' ) return;

      var confirm = window.confirm("¿Deseas eliminar este dispositivo?");
      if ( ! confirm ) return;

      $.ajax({
        data: {
          'method' : 'deleteDevice',
          'id' :  deviceId
        },
        success: function( response ) {
          if ( response.response ) {
            showDevices();
          } else {
            $( device ).find( '.error-message' ).text( 'No se ha podido eliminar el dispositivo.' ).show();
          }
        },
        error: function() {
          $( device ).find( '.error-message' ).text( 'Ha ocurrido un error al eliminar el dispositivo.' ).show();
        }
      });

    });

});