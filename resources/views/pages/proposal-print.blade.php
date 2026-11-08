<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Create Proposal</title>
    {{-- <link rel="stylesheet" href="{{asset('asset/proposal/style.css')}}" /> --}}
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.5/jspdf.min.js"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { 
  size: 25cm 30cm ; 
  }

    </style>
  </head>
  <body>
    <header class="clearfix">
      
      <h1>Client Proposal</h1>
      
    </header>
    <main>
      <input type="button" id="create_pdf" value="Generate PDF">
      <form class="form" style="max-width: none; width: 1005px;">
        <div style="width: 705px;">
        <img src="{{ asset('asset/1.jpg') }}" alt="" style="height: 140px; width: 100%; {{ (request()->send == '1')? 'display:none;' : '' }}">
        </div> 
        <div><br>

          {{-- <div id="company" class="clearfix" wid> --}}
            {{-- <div>Company Name</div>
            <div>455 Foggy Heights,<br /> AZ 85004, US</div> --}}
            {{-- <div>{{ date('d-m-y') }}</div> --}}
            {{-- <div><a href="mailto:company@example.com">company@example.com</a></div> --}}
          {{-- </div> --}}
          <div id="" style="font-size: 15px; {{ (request()->send == '1')? 'margin-top: 180px;' : '' }}">
            <div>
              <table style="width: 1005px;" border="0">
                <tr>
                  <td>{{ request()->company }}</td>
                  <td style="text-align: right;"><span style="margin-right: 300px;">Date: {{ date('d-M-Y') }}</span></td>
                </tr>
              </table>
            </div>
            <div>{{ request()->name }}</div>
            <div>{{ request()->address }}</div>
            <div>Call: {{ request()->mobile }}</div>
          </div>
        </div>
          <br>
      <div id="" style="font-size: 15px;">
        <div>Subject: Proposal For {{ implode(',',request()->what) }}</div>
      </div><br>
      <div id="" style="font-size: 15px; height: 530px;">
        <div>Dear Sir,</div>
        <div >
          <p style="text-align: justify;">IGL Web Ltd. is an organization proposed by a team of self-motivated young ICT professionals and business specialist  to  render  highly  sophisticated,  value  based  and  quality services in  the  field  of Information  & Communication Technology to the community as a whole. Company vision is to enable people at all levels to reach the appropriate, advanced, ultimate computer-based technologies and solutions through an affordable and agreeable means.</p> 
          <p style="text-align: justify;">With the recent developments of computer hardware, software and communications facilities combined innovations in IT, we have proposed for the following business concepts of multiple services for multiple customers:</p>
          
            <ul style="list-style-type: none; margin-left: 50px; margin-top: -18px;">
              <li>Wed Design & Development</li>
              <li>CCTV/IP Camera Solution</li>
              <li>Web Application Development</li>
              <li>Web Hosting & Domain Registration</li>
              <li>IT Consultancy</li>
            </ul>
          
        </div>
        <p>It will be a great pleasure for us if you kindly give us the opportunity to Host your organization’s website. Thanks,</p>
        <br>
        <div>
            <div>{{ Auth::user()->au_company_name }}</div>
            <div>{{ Auth::user()->au_name }}</div>
            {{-- <div>CTO</div> --}}
            <div>Tel: +88 {{ Auth::user()->au_mobile }}</div>
            <div>E-mail: {{ Auth::user()->au_email }}</div>
        </div>
    </div>
    
    <footer style="height: 40px; margin-top: 130px; background-color: #34a12c; {{ (request()->send == '1')? 'display:none;' : '' }}">
      <p style="text-align: center; font-size: 13px;">House # 33 (4th Floor), Road # 4, Dhanmondi, Dhaka-1205, Cell 01614 445 932, 01823 03 77 26, Ph: 02-967 5671</p>
      <p style="text-align: center; margin-top: -15px;">Web: www.iglweb.com, www.iglgroup.org</p>
    </footer>
    </main>
  </form>
  </body>
</html>
<script>
  (function () {
      var
       form = $('.form'),
       cache_width = form.width(),
       a4 = [595.28, 841.89]; // for a4 size paper width and height

      $('#create_pdf').on('click', function () {
          $('body').scrollTop(0);
          createPDF();
      });
      //create pdf
      function createPDF() {
          getCanvas().then(function (canvas) {
              var
               img = canvas.toDataURL("image/png"),
               doc = new jsPDF({
                   unit: 'px',
                   format: 'a4'
               });
              doc.addImage(img, 'JPEG', 20, 20);
              doc.save('{{ request()->name }}{{ implode('',request()->what) }}.pdf');
              form.width(cache_width);
          });
      }

      // create canvas object
      function getCanvas() {
          form.width((a4[0] * 1.33333) - 80).css('max-width', 'none');
          return html2canvas(form, {
              imageTimeout: 2000,
              removeContainer: true
          });
      }

  }());
</script>
<script>
  (function ($) {
      $.fn.html2canvas = function (options) {
          var date = new Date(),
          $message = null,
          timeoutTimer = false,
          timer = date.getTime();
          html2canvas.logging = options && options.logging;
          html2canvas.Preload(this[0], $.extend({
              complete: function (images) {
                  var queue = html2canvas.Parse(this[0], images, options),
                  $canvas = $(html2canvas.Renderer(queue, options)),
                  finishTime = new Date();

                  $canvas.css({ position: 'absolute', left: 0, top: 0 }).appendTo(document.body);
                  $canvas.siblings().toggle();

                  $(window).click(function () {
                      if (!$canvas.is(':visible')) {
                          $canvas.toggle().siblings().toggle();
                          throwMessage("Canvas Render visible");
                      } else {
                          $canvas.siblings().toggle();
                          $canvas.toggle();
                          throwMessage("Canvas Render hidden");
                      }
                  });
                  throwMessage('Screenshot created in ' + ((finishTime.getTime() - timer) / 1000) + " seconds<br />", 4000);
              }
          }, options));

          function throwMessage(msg, duration) {
              window.clearTimeout(timeoutTimer);
              timeoutTimer = window.setTimeout(function () {
                  $message.fadeOut(function () {
                      $message.remove();
                  });
              }, duration || 2000);
              if ($message)
                  $message.remove();
              $message = $('<div ></div>').html(msg).css({
                  margin: 0,
                  padding: 10,
                  background: "#000",
                  opacity: 0.7,
                  position: "fixed",
                  top: 10,
                  right: 10,
                  fontFamily: 'Tahoma',
                  color: '#fff',
                  fontSize: 12,
                  borderRadius: 12,
                  width: 'auto',
                  height: 'auto',
                  textAlign: 'center',
                  textDecoration: 'none'
              }).hide().fadeIn().appendTo('body');
          }
      };
  })(jQuery);
</script>