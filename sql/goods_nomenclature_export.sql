CREATE OR REPLACE FUNCTION ml.goods_nomenclature_export(pchapter text)
 RETURNS TABLE(goods_nomenclature_sid integer, goods_nomenclature_item_id character varying, producline_suffix character varying, validity_start_date timestamp without time zone, validity_end_date timestamp without time zone, description text, number_indents integer, goods_nomenclature_description_period_sid integer, desc_validity_start_date timestamp without time zone, desc_validity_end_date timestamp without time zone, nice_description text, chapter text, node text, leaf text)
 LANGUAGE plpgsql
AS $function$

#variable_conflict use_column

BEGIN

IF pchapter = '' THEN
pchapter = '%';
END IF;

/* temporary table contains results of query plus a placeholder column for leaf - defaulted to 0
node column has the significant digits used to find child nodes having the same significant digits.
The basic query retrieves all current (and future) nomenclature with indents and descriptions */

DROP TABLE IF EXISTS tmp_nomenclature;

CREATE TEMP TABLE tmp_nomenclature ON COMMIT DROP AS
SELECT gn.goods_nomenclature_sid, gn.goods_nomenclature_item_id, gn.producline_suffix, gn.validity_start_date, gn.validity_end_date, 
regexp_replace(gnd.description, E'[\\n\\r]+', ' ', 'g') as description,
gni.number_indents, 
gndp.goods_nomenclature_description_period_sid, gndp.validity_start_date "desc_validity_start_date", gndp.validity_end_date "desc_validity_end_date",
concat(repeat (' -', gni.number_indents), ' ', regexp_replace(gnd.description, E'[\\n\\r]+', ' ', 'g')) "nice_description",
left (gn.goods_nomenclature_item_id, 2) "chapter",
REGEXP_REPLACE (gn.goods_nomenclature_item_id, '(00)+$', '') AS "node",
'0' AS "leaf"
FROM goods_nomenclatures gn
JOIN goods_nomenclature_descriptions gnd ON gnd.goods_nomenclature_sid = gn.goods_nomenclature_sid
JOIN goods_nomenclature_description_periods gndp ON gndp.goods_nomenclature_description_period_sid = gnd.goods_nomenclature_description_period_sid
JOIN goods_nomenclature_indents gni ON gni.goods_nomenclature_sid = gn.goods_nomenclature_sid
WHERE (gn.validity_end_date IS NULL OR gn.validity_end_date >= CURRENT_DATE)
AND gn.goods_nomenclature_item_id LIKE pchapter
AND gndp.goods_nomenclature_description_period_sid IN
(SELECT MAX (gndp2.goods_nomenclature_description_period_sid)
FROM goods_nomenclature_description_periods gndp2
WHERE gndp2.goods_nomenclature_sid = gnd.goods_nomenclature_sid
AND gndp2.validity_start_date < CURRENT_DATE
UNION
SELECT gndp3.goods_nomenclature_description_period_sid
FROM goods_nomenclature_description_periods gndp3
WHERE gndp3.goods_nomenclature_sid = gnd.goods_nomenclature_sid
AND gndp3.validity_start_date >= CURRENT_DATE)
AND gni.goods_nomenclature_indent_sid IN
(SELECT MAX (gni2.goods_nomenclature_indent_sid)
FROM goods_nomenclature_indents gni2
WHERE gni2.goods_nomenclature_sid = gn.goods_nomenclature_sid
AND gni2.validity_start_date < CURRENT_DATE
UNION
SELECT gni3.goods_nomenclature_indent_sid
FROM goods_nomenclature_indents gni3
WHERE gni3.goods_nomenclature_sid = gn.goods_nomenclature_sid
AND gni3.validity_start_date >= CURRENT_DATE);

/* index to speed up child node matching - need to perf test to see if any use */
CREATE INDEX t1_i_nomenclature 
ON tmp_nomenclature (goods_nomenclature_sid, goods_nomenclature_item_id);

/* cursor loops through result set to identify if nodes are leaf and updates the flag if so */
DECLARE
cur_nomenclature CURSOR FOR 
SELECT * FROM tmp_nomenclature;

BEGIN

FOR nom_record IN cur_nomenclature LOOP
Raise Notice 'goods nomenclature item id %', nom_record.goods_nomenclature_item_id;

/* Leaf nodes have to have pls of 80 and no children having the same nomenclature code */
IF nom_record.producline_suffix = '80' THEN
IF LENGTH (nom_record.node) = 10 OR NOT EXISTS (SELECT 1 
FROM tmp_nomenclature 
WHERE goods_nomenclature_item_id LIKE CONCAT(nom_record.node,'%')
AND goods_nomenclature_item_id <> nom_record.goods_nomenclature_item_id) THEN

UPDATE tmp_nomenclature tn
SET leaf = '1'
WHERE goods_nomenclature_sid = nom_record.goods_nomenclature_sid;

END IF;
END IF;

END LOOP;

END;

RETURN QUERY 
SELECT * 
FROM tmp_nomenclature;

END;

$function$
;
