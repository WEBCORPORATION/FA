<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU Affero General Public License,
	AGPL, as published by the Free Software Foundation, either version 
	3 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/agpl-3.0.html>.
***********************************************************************/
$page_security = 1;
$path_to_root="../..";

include($path_to_root . "/includes/session.inc");

page(_("View Inventory Transfer"), true);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

if (isset($_GET["trans_no"]))
{
	$trans_no = $_GET["trans_no"];
}

$transfer_items = get_stock_transfer($trans_no);

$from_trans = $transfer_items[0];
$to_trans = $transfer_items[1];

display_heading(systypes::name(systypes::location_transfer()) . " #$trans_no");

echo "<br>";
start_table("$table_style2 width=90%");

start_row();
label_cells(_("Item"), $from_trans['stock_id'] . " - " . $from_trans['description'], "class='tableheader2'");
label_cells(_("From Location"), $from_trans['location_name'], "class='tableheader2'");
label_cells(_("To Location"), $to_trans['location_name'], "class='tableheader2'");
end_row();
start_row();
label_cells(_("Reference"), $from_trans['reference'], "class='tableheader2'");
$adjustment_type = get_movement_type($from_trans['person_id']) ;
label_cells(_("Adjustment Type"), $adjustment_type['name'], "class='tableheader2'");
label_cells(_("Date"), sql2date($from_trans['tran_date']), "class='tableheader2'");
end_row();

comments_display_row(systypes::location_transfer(), $trans_no);

end_table(1);

echo "<br>";
start_table("$table_style width=90%");

$th = array(_("Item"), _("Description"), _("Quantity"), _("Units"));
table_header($th);
$transfer_items = get_stock_moves(systypes::location_transfer(), $trans_no);
$k = 0;
while ($item = db_fetch($transfer_items))
{
	if ($item['loc_code'] == $to_trans['loc_code'])
	{
        alt_table_row_color($k);

        label_cell($item['stock_id']);
        label_cell($item['description']);
        qty_cell($item['qty'], false, get_qty_dec($item['stock_id']));
        label_cell($item['units']);
        end_row();;
	}
}

end_table(1);

is_voided_display(systypes::location_transfer(), $trans_no, _("This transfer has been voided."));

end_page(true);
?>