CREATE TABLE tx_j77template_fedttc_feditor_columnrow_columns ( 
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    extended int(5) DEFAULT '0' NOT NULL,    
    col_lg TINYTEXT,
    col_md TINYTEXT,
    col_sm TINYTEXT,
    col_xs TINYTEXT,
    order_lg TINYTEXT,
    order_md TINYTEXT,
    order_sm TINYTEXT,
    order_xs TINYTEXT,
    offset_lg TINYTEXT,
    offset_md TINYTEXT,
    offset_sm TINYTEXT,
    offset_xs TINYTEXT,
    additional_col_class TINYTEXT,
    parent_column_row TINYTEXT,
    sorting int(11) DEFAULT '0' NOT NULL,
    PRIMARY KEY (uid)
);

CREATE TABLE tt_content ( 
    feditorce_feditor_columnrow_content_width TINYTEXT,
    feditorce_feditor_columnrow_select_background TINYTEXT,
    feditorce_feditor_columnrow_row_background TINYTEXT,
    feditorce_feditor_columnrow_row_user_background TINYTEXT,
    feditorce_feditor_columnrow_row_background_image int(11) unsigned NOT NULL default '0',
    feditorce_feditor_columnrow_additional_row_class TINYTEXT,
    feditorce_feditor_columnrow_columns int(11) unsigned NOT NULL default '0',
);