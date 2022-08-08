<?php

// This is the run once initial import script

require_once('../config.php');

$in = fopen('../data/ipni/new.csv', 'r');

// drop the header
fgetcsv($in, null, '|');

// work through the lines
$count = 0;

$statement =  $mysqli->prepare("
    INSERT INTO `ipni`
    (
        `id`,
        `authors_t`,
        `basionym_s_lower`,
        `basionym_author_s_lower`,
        `lookup_basionym_id`,
        `bibliographic_reference_s_lower`,
        `bibliographic_type_info_s_lower`,
        `reference_collation_s_lower`,
        `collection_date_as_text_s_lower`,
        `collection_day_1_s_lower`,
        `collection_day_2_s_lower`,
        `collection_month_1_s_lower`,
        `collection_month_2_s_lower`,
        `collection_number_s_lower`,
        `collection_year_1_s_lower`,
        `collection_year_2_s_lower`,
        `collector_team_as_text_t`,
        `lookup_conserved_against_id`,
        `lookup_correction_of_id`,
        `date_created_date`,
        `date_last_modified_date`,
        `distribution_s_lower`,
        `east_or_west_s_lower`,
        `family_s_lower`,
        `taxon_scientific_name_s_lower`,
        `taxon_sci_name_suggestion`,
        `genus_s_lower`,
        `geographic_unit_as_text_s_lower`,
        `hybrid_b`,
        `hybrid_genus_b`,
        `lookup_hybrid_parent_id`,
        `hybrid_parents_s_lower`,
        `infra_family_s_lower`,
        `infra_genus_s_lower`,
        `infraspecies_s_lower`,
        `lookup_isonym_of_id`,
        `lookup_later_homonym_of_id`,
        `latitude_degrees_s_lower`,
        `latitude_minutes_s_lower`,
        `latitude_seconds_s_lower`,
        `locality_s_lower`,
        `longitude_degrees_s_lower`,
        `longitude_minutes_s_lower`,
        `longitude_seconds_s_lower`,
        `name_status_s_lower`,
        `name_status_bot_code_type_s_lower`,
        `name_status_editor_type_s_lower`,
        `nomenclatural_synonym_s_lower`,
        `lookup_nomenclatural_synonym_id`,
        `north_or_south_s_lower`,
        `original_basionym_s_lower`,
        `original_basionym_author_team_s_lower`,
        `original_hybrid_parentage_s_lower`,
        `original_remarks_s_lower`,
        `original_replaced_synonym_s_lower`,
        `original_taxon_distribution_s_lower`,
        `lookup_orthographic_variant_of_id`,
        `other_links_s_lower`,
        `lookup_parent_id`,
        `publication_s_lower`,
        `lookup_publication_id`,
        `publication_year_i`,
        `publication_year_full_s_lower`,
        `publication_year_note_s_lower`,
        `publishing_author_s_lower`,
        `rank_s_alphanum`,
        `reference_t`,
        `reference_remarks_s_lower`,
        `remarks_s_lower`,
        `lookup_replaced_synonym_id`,
        `lookup_same_citation_as_id`,
        `score_s_lower`,
        `species_s_lower`,
        `species_author_s_lower`,
        `lookup_superfluous_name_of_id`,
        `suppressed_b`,
        `top_copy_b`,
        `lookup_type_id`,
        `type_locations_s_lower`,
        `type_name_s_lower`,
        `type_remarks_s_lower`,
        `type_chosen_by_s_lower`,
        `type_note_s_lower`,
        `detail_author_team_ids`,
        `detail_species_author_team_ids`,
        `page_as_text_s_lower`,
        `citation_type_s_lower`,
        `lookup_validation_of_id`,
        `version_s_lower`,
        `powo_b`,
        `sortable`,
        `family_taxon_name_sortable`
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? 
    );
");

while($line = fgetcsv($in, null, '|')){

    //print_r($line);

    $statement->bind_param("ssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss",
        $line[0],
        $line[1],
        $line[2],
        $line[3],
        $line[4],
        $line[5],
        $line[6],
        $line[7],
        $line[8],
        $line[9],
        $line[10],
        $line[11],
        $line[12],
        $line[13],
        $line[14],
        $line[15],
        $line[16],
        $line[17],
        $line[18],
        $line[19],
        $line[20],
        $line[21],
        $line[22],
        $line[23],
        $line[24],
        $line[25],
        $line[26],
        $line[27],
        $line[28],
        $line[29],
        $line[30],
        $line[31],
        $line[32],
        $line[33],
        $line[34],
        $line[35],
        $line[36],
        $line[37],
        $line[38],
        $line[39],
        $line[40],
        $line[41],
        $line[42],
        $line[43],
        $line[44],
        $line[45],
        $line[46],
        $line[47],
        $line[48],
        $line[49],
        $line[50],
        $line[51],
        $line[52],
        $line[53],
        $line[54],
        $line[55],
        $line[56],
        $line[57],
        $line[58],
        $line[59],
        $line[60],
        $line[61],
        $line[62],
        $line[63],
        $line[64],
        $line[65],
        $line[66],
        $line[67],
        $line[68],
        $line[69],
        $line[70],
        $line[71],
        $line[72],
        $line[73],
        $line[74],
        $line[75],
        $line[76],
        $line[77],
        $line[78],
        $line[79],
        $line[80],
        $line[81],
        $line[82],
        $line[83],
        $line[84],
        $line[85],
        $line[86],
        $line[87],
        $line[88],
        $line[89],
        $line[90],
        $line[91]
    );

    $statement->execute();

    if($statement->error){
        print_r($line);
        echo "\n" . $statement->error;
        exit;
    }

    $count++;

    if($count % 10000 == 0){
        echo "\n" . number_format($count, 0);
    }

}
