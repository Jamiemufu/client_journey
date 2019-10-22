<div style="position: fixed; z-index: 2147483647; top: 0; left: 0; right: 0; padding: 15px 15px 0 15px; background-color: rgba(35, 35, 35, 0.95); box-shadow: 0 0 2px 2px rgba(0, 0, 0, 0.5); font-family: 'Open Sans', sans-serif; font-size: 12px;">

    <div>

        @foreach ($log_level_stats as $log_level => $details)

            <span style="display: inline-block; font-size: 14px; background-color: {{{ $details['colour'] }}}; color: #ffffff; border-radius: 2px; padding: 5px; margin: 0 7px 15px 0;">{{{ $details['value'] }}}</span>

        @endforeach

    </div>

    @foreach ($errors as $error)

        <details style="display: block; padding-bottom: 15px; color: rgb(255, 255, 255); padding-top: 15px; border-top: double rgba(255, 255, 255, 0.1);">

            <summary style="outline: none; cursor: pointer;">
                <span style="font-weight: bold; font-size: 16px; vertical-align: middle;">[{{{ $error['additional']['error_code'] }}}] {{{ html_entity_decode($error['message'], ENT_QUOTES) }}}</span>
            </summary>

            <p style="font-size: 14px; margin: 10px 0 0 15px; font-weight: bold; color: rgba(255, 255, 255, 0.85);">{{{ $error['additional']['file'] }}} &#40;line {{{ number_format($error['additional']['line_number']) }}}&#41;</p>

            <div style="max-height: 175px; overflow: auto;">

                <table style="margin-left: 15px;">

                    <tr><td valign="top" style="padding-top: 10px;"><pre style="font-family: 'Andale Mono', Courier, monospace; font-size: 13px; word-break: normal; margin: 0; font-weight: bold; color: rgba(255, 255, 255, 0.4); background-color: transparent; border: none;">Line&nbsp;{{{ number_format($error['additional']['line_number']) }}}</pre></td><td style="width: 25px;"> </td><td style="padding-top: 10px;" valign="top"><pre style="font-family: 'Andale Mono', Courier, monospace; font-size: 13px; word-break: normal; margin: 0; color: rgba(255, 255, 255, 0.4); white-space: pre-wrap; background-color: transparent; border: none;">{{{ trim($error['additional']['affected_line']) }}}</pre></td></tr>

                    @if (!empty($error['additional']))

                        @foreach ($error['additional'] as $var_name => $var_value)

                            @if (in_array($var_name, $allowed_additional) AND !empty($var_value))

                            <tr>

                                <td valign="top" style="padding-top: 10px;">

                                    <pre style="font-family: 'Andale Mono', Courier, monospace; font-size: 13px; word-break: normal; margin: 0; font-weight: bold; color: rgba(255, 255, 255, 0.4); background-color: transparent; border: none;">{{{ html_entity_decode(ucwords($var_name), ENT_QUOTES) }}}</pre>

                                </td>

                                <td style="width: 25px;"> </td>

                                <td style="padding-top: 10px;" valign="top">

                                    <pre style="font-family: 'Andale Mono', Courier, monospace; font-size: 13px; word-break: normal; margin: 0; color: rgba(255, 255, 255, 0.4); white-space: pre-wrap; background-color: transparent; border: none;">@if ($var_name == 'backtrace'){{{ implode("\n\n", $var_value) }}}@else{{{ trim(html_entity_decode(print_r($var_value, true), ENT_QUOTES)) }}}@endif</pre>

                                </td>

                            </tr>

                            @endif

                        @endforeach

                    @endif

                </table>

            </div>

        </details>

    @endforeach

</div>