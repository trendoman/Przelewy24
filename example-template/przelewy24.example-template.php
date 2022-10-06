<?php require_once( 'couch/cms.php' ); ?>
<cms:template title="Przelewy24" clonable='1' icon="credit-card" order="5">

  <cms:editable
      name='przelewy24_sessionid'
      type='uid'
      label='Transaction Number'
      desc='will be generated automatically'
      search_type='integer'
      begin_from='-1'
      min_length='7'
      prefix=''
      suffix='--[DD].[MM].[YYYY]--[HH]:[N]'
  />

  <cms:editable name='przelewy24_orderid' type='text' label='Order ID' desc='assigned by payment processor' />
  <cms:editable name='przelewy24_token' type='text' label='Token' desc='assigned by payment processor' />
  <cms:editable name='przelewy24_status' type='radio' label='Status' desc='updates automatically' opt_values='new=-|registered=0|notified=1|verified=2|confirmed=3' />

  <cms:editable name='group_debug' label='Communication' type='group' order='1000' collapsed='0'>
    <cms:editable name='przelewy24_registration' type='textarea' label='Registration' desc='sent by payment processor' height='45' order='100'/>
    <cms:editable name='przelewy24_notification' type='textarea' label='Notification' desc='sent by payment processor' height='70' order='200'/>
    <cms:editable name='przelewy24_verification' type='textarea' label='Verification' desc='sent by payment processor' height='45' order='300'/>
    <cms:editable name='przelewy24_host' type='dropdown' label='Host' desc='selected automatically, depends on "sandbox" tag parameter' opt_values='-=-|Sandbox=0|Live=1' order='400' opt_selected='-'/>
    <cms:editable name='przelewy24_transactionid' type='text' label='Transaction ID' desc='assigned by payment processor' order='500'/>
  </cms:editable>


  <cms:globals>
    <cms:editable type='text' name='przelewy24_merchantid' label='Merchant ID' order='10'>000</cms:editable>
    <cms:editable type='text' name='przelewy24_crc' label='CRC key' order='20'>abc</cms:editable>
    <cms:editable type='text' name='przelewy24_reportkey' label='Klucz do raportów' order='30' >abc</cms:editable>

    <cms:editable type='checkbox' name='przelewy24_channel' label='Channel' desc='activate the specific channels'
    opt_values='
      card + ApplePay + GooglePay = 1 ||
      transfer = 2 ||
      traditional transfer = 4 ||
      N/A = 8 ||
      all 24/7 - makes available all payment methods = 16 ||
      use pre-payment = 32 ||
      only pay-by-link methods = 64 ||
      instalment payment forms = 128 ||
      wallets = 256 ||
      card = 4096
      '
    opt_selected = '16'
    order='40'/>

    <cms:editable type='text' name='przelewy24_email' label='Email' desc='for reports from Przelewy24 Service' order='50'>anton.cms@ya.ru</cms:editable>

    <cms:config_form_view>
      <cms:field 'Currency' order='100' >PLN</cms:field>
      <cms:field 'Country' order='110' >PL</cms:field>
      <cms:field 'Language' order='120' >pl</cms:field>
    </cms:config_form_view>

  </cms:globals>


  <cms:config_form_view>
    <cms:persist k_page_title="<cms:get 'frm_k_page_title' default="<cms:random_name />" />" />

    <cms:field 'k_page_title' hide='1' />
    <cms:field 'k_page_name' hide='1' />

    <cms:field 'przelewy24_token'>
      <cms:input name=k_field_name type='bound' trust_mode='1'/>
      <cms:if przelewy24_token && przelewy24_host ne '-'>
        <span class="desc">
          <cms:if przelewy24_host eq '0'>
            SANDBOX:
            <a href="https://sandbox-go.przelewy24.pl/trnStatus/<cms:show k_field_data />" target="_blank">View status</a>,&nbsp;
            <a href="https://sandbox-go.przelewy24.pl/trnResult/<cms:show k_field_data />" target="_blank">View result</a>
          <cms:else_if przelewy24_host eq '1' />
            LIVE:
            <a href="https://sandbox-go.przelewy24.pl/trnStatus/<cms:show k_field_data />" target="_blank">View status</a>,&nbsp;
            <a href="https://sandbox-go.przelewy24.pl/trnResult/<cms:show k_field_data />" target="_blank">View result</a>
          </cms:if>
        </span>
      </cms:if>
    </cms:field>

    <cms:field 'przelewy24_orderid'>
      <cms:input name=k_field_name type='bound' trust_mode='1'/>
      <cms:if przelewy24_orderid && przelewy24_host ne '-'>
        <span class="desc">
          <cms:if przelewy24_host eq '0'>
          SANDBOX: <a href="https://sandbox.przelewy24.pl/panel/transakcja.php?id=<cms:show k_field_data />" target="_blank">View panel</a>
          <cms:else_if przelewy24_host eq '1' />
          LIVE: <a href="https://sandbox.przelewy24.pl/panel/transakcja.php?id=<cms:show k_field_data />" target="_blank">View panel</a>
          </cms:if>
        </span>
      </cms:if>
    </cms:field>

  </cms:config_form_view>

  <cms:config_list_view exclude='default-page-for-pay-przelewy24-php' orderby='przelewy24_sessionid' order='desc' limit='45'>

    <cms:field 'k_selector_checkbox' />
    <cms:field 'k_page_title' sortable='0' header='Transaction ID' >
      <a href="<cms:admin_link />"><cms:show przelewy24_sessionid /></a>
    </cms:field>
    <cms:field 'k_page_date' />
    <cms:field 'k_actions' />

    <cms:html
      memo='Have two dropdown fields to quick-filter transactions by year and month'>

      <cms:set selected_year_0101 = "<cms:date "<cms:gpc 'start_on' />" format='Y-01-01' />" />
      <cms:set selected_year_next = "<cms:date "<cms:gpc 'start_on' /> +1 year" format='Y-01-01' />" />

      <cms:capture into='allYears'>
        .:: Year ::. @ <cms:show k_route_link /> |
        <cms:archives masterpage=k_route_masterpage type='yearly' show_future_entries='1'>
          <cms:date k_archive_date format='Y' /> @
          <cms:add_querystring k_route_link "start_on=<cms:date k_archive_date format='Y-m-d' />&stop_before=<cms:date k_next_archive_date format='Y-m-d' />" /> |
        </cms:archives>
      </cms:capture>

      <cms:capture into='allYearsSelected'>
        <cms:if "<cms:gpc 'start_on' />">
          <cms:add_querystring k_route_link "start_on=<cms:show selected_year_0101 />&stop_before=<cms:show selected_year_next />" />
        <cms:else />
          <cms:show k_route_link />
        </cms:if>
      </cms:capture>

      <cms:capture into='allMonths'>
        .:: Month ::. @
        <cms:if "<cms:gpc 'start_on' />">
          <cms:add_querystring k_route_link "start_on=<cms:show selected_year_0101 />&stop_before=<cms:show selected_year_next />" />
        <cms:else />
          <cms:show k_route_link />
        </cms:if>
        |
        <cms:archives masterpage=k_route_masterpage type='monthly' show_future_entries='1' start_on=selected_year_0101 stop_before=selected_year_next >
          <cms:date k_archive_date format='%Y - %B' locale='polish' charset='Windows-1250' /> @
          <cms:add_querystring k_route_link "start_on=<cms:date k_archive_date format='Y-m-d' />&stop_before=<cms:date k_next_archive_date format='Y-m-d' />" /> |
        </cms:archives>
      </cms:capture>

      <cms:capture into='allMonthsSelected'>
        <cms:add_querystring k_route_link
          "start_on=<cms:date "<cms:gpc 'start_on' />" format='Y-m-d' />&stop_before=<cms:date "<cms:gpc 'stop_before' />" format='Y-m-d' />"
        />
      </cms:capture>

      <cms:input type='dropdown' name='yearly' opt_values=allYears opt_selected=allYearsSelected val_separator='@' />
      <cms:input type='dropdown' name='monthly' opt_values=allMonths opt_selected=allMonthsSelected val_separator='@' />

      <cms:php>
        global $CTX;
        $CTX->set('k_selected_start_on', $_GET['start_on'], 'parent');
        $CTX->set('k_selected_stop_before', $_GET['stop_before'], 'parent');
      </cms:php>

    </cms:html>

    <cms:script>
      $("select#yearly").on('change', function(){ window.location.href = $(this).val(); });
      $("select#monthly").on('change', function(){ window.location.href = $(this).val(); });
    </cms:script>

  </cms:config_list_view>



</cms:template>
<cms:if k_is_page>

<cms:else />

  <cms:test ignore='0'>

    <cms:if k__gpc.GET.generate eq '1'>

      <cms:przelewy24_paylink
        sandbox = '1'
        debug = '1'
        regulationAccept = '0'
        return_url = k_template_link
        status_url = k_template_link
        description = "Zamówienie nr. 1005"
        amount = '333.50'
        shipping = '30'
        into='mylink'
      />


      <cms:if mylink>
        <button onclick="location.href='<cms:show mylink />'" type="button">PAY</button>
      <cms:else />
        <button onclick="location.href='<cms:add_querystring k_page_link 'generate=1' />'" type="button">GENERATE LINK</button>
      </cms:if>

    <cms:else />
      <button onclick="location.href='<cms:add_querystring k_page_link 'generate=1' />'" type="button">GENERATE LINK</button>
    </cms:if>

  </cms:test>

  <cms:test ignore='0'>

    <cms:if
      k__ip eq '127.0.0.1' ||
      k__ip eq '5.252.202.255' ||
      k__ip eq '5.252.202.254' ||
      "<cms:call 'is-ip-within' ip=k__ip range='91.216.191.181 - 91.216.191.185' />"

      >
      <cms:przelewy24_processor sandbox='1' debug='1' />

      <cms:call 'log-html' msg="<cms:show_json k__gpc />" file='p24_log.html' />
      <cms:log msg="<cms:show_json k__gpc as_html='0' />" file='p24_log.txt' />

    </cms:if>


  </cms:test>

</cms:if>
<?php COUCH::invoke(); ?>
