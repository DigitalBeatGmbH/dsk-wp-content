
<p>
    UTM parameters work only if the tracking is active.
</p>
<table class="form-table">
    <tr valign="top">
        <th>UTM Source</th>
        <td>
            <?php $controls->text('options_utm_source', 50); ?>
            <p class="description">
                Should set as "newsletter-{email_id}" and it's mandatory for Google. "{email_id}" is replaced with the
                newsletter unique id. Automated newsletter, autoresponders and other non standard newsletter use a different
                source like automated-{channel numer}-{email id}.
            </p>
        </td>
    </tr>
    
    <tr valign="top">
        <th>UTM Campaign</th>
        <td>
            <?php $controls->text('options_utm_campaign', 50); ?>
            <p class="description">
                This is the campaign name Newsletter-{email_id}
            </p>
        </td>
    </tr>

    <tr valign="top">
        <th>UTM Medium</th>
        <td>
            <?php $controls->text('options_utm_medium', 50); ?>
            <p class="description">
                Should be set to "email" since this is the only medium used.
            </p>
        </td>
    </tr>

    <tr valign="top">
        <th>UTM Term</th>
        <td>
            <?php $controls->text('options_utm_term', 50); ?>
            <p class="description">
                Usually empty can be used on specific newsletters but it is more related to keyword based advertising.
            </p>
        </td>
    </tr>

    <tr valign="top">
        <th>UTM Content</th>
        <td>
            <?php $controls->text('options_utm_content', 50); ?>
            <p class="description">
                Usually empty can be used on specific newsletters.
            </p>
        </td>
    </tr>

</table>
