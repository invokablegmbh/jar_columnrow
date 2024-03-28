CREATE TABLE tx_jarcolumnrow_columns ( 
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    extended int(5) DEFAULT '0' NOT NULL,
    title TINYTEXT,
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
    colPos BIGINT unsigned DEFAULT '0' NOT NULL,
    columnrow_content_width TINYTEXT,
    columnrow_select_background TINYTEXT,
    columnrow_row_background TINYTEXT,
    columnrow_row_user_background TINYTEXT,
    columnrow_row_background_image int(11) unsigned NOT NULL default '0',
    columnrow_additional_row_class TINYTEXT,
    columnrow_columns int(11) unsigned NOT NULL default '0',
);
