        <li>
            <label for="form_custom_currency">Currency</label>
            <select id="form_custom_currency" name="form_custom_currency" style="width:200px">
                <option value="">Default currency (<?php echo $this->gf_get_default_currency() ?>)</option>
                <?php foreach (RGCurrency::get_currencies() as $code => $currency): ?>
                <option
                    <?php if (rgar($form, 'custom_currency') === $code): ?>
                    selected="selected"
                    <?php endif; ?>
                    value="<?php echo $code ?>"><?php echo $currency['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </li>

