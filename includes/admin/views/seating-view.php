<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap" id="kq-seating-admin">
    <h1><?php _e( 'Seating Map Builder', 'kueue-events-core' ); ?></h1>
    
    <p class="description">
        <?php _e( 'Design your venue sections, rows, and individual seats. Maps created here can be assigned to Ticket Types.', 'kueue-events-core' ); ?>
    </p>

    <style>
        .seating-editor { display: grid; grid-template-columns: 1fr 400px; gap: 25px; margin-top: 20px; }
        .map-preview { background: #f0f0f1; border: 2px dashed #ccc; border-radius: 12px; min-height: 400px; display: flex; align-items: center; justify-content: center; position: relative; overflow: auto; padding: 20px; }
        .config-pane { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
        
        .map-section { background: #fff; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px; padding: 15px; }
        .map-row { display: flex; gap: 5px; margin-top: 10px; flex-wrap: wrap; }
        .map-seat { width: 25px; height: 25px; background: #eef7ff; border: 1px solid #0073aa; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.6em; color: #0073aa; cursor: default; }
        
        .json-editor { width: 100%; height: 300px; font-family: monospace; font-size: 12px; margin-top: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 5px; }
    </style>

    <div class="seating-editor">
        <div class="map-preview" id="map-preview">
            <p id="preview-placeholder"><?php _e( 'Live Preview will appear here after saving.', 'kueue-events-core' ); ?></p>
        </div>

        <div class="config-pane">
            <div class="form-group">
                <label><?php _e( 'Map Name', 'kueue-events-core' ); ?></label>
                <input type="text" id="map-name" class="widefat" placeholder="Main Hall A">
            </div>

            <div class="form-group">
                <label><?php _e( 'Configuration (JSON)', 'kueue-events-core' ); ?></label>
                <textarea id="map-json" class="json-editor"></textarea>
                <p class="description">
                    <?php _e( 'Format: {"sections": [{"name": "VIP", "rows": [{"label": "A", "seats": 10}]}]}', 'kueue-events-core' ); ?>
                </p>
            </div>

            <div class="form-group">
                <button class="button button-primary button-large" id="save-map-btn"><?php _e( 'Save Map & Preview', 'kueue-events-core' ); ?></button>
                <button class="button" id="load-sample-btn"><?php _e( 'Load Sample JSON', 'kueue-events-core' ); ?></button>
            </div>
            
            <hr>
            <h3><?php _e( 'Existing Maps', 'kueue-events-core' ); ?></h3>
            <ul id="existing-maps-list"></ul>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        const restUrl = '<?php echo esc_url_raw( rest_url( "kq/v1/seating" ) ); ?>';
        const nonce = '<?php echo wp_create_nonce( "wp_rest" ); ?>';

        const sampleJson = {
            sections: [
                {
                    name: "Front Row",
                    rows: [
                        { label: "A", seats: 8 },
                        { label: "B", seats: 10 }
                    ]
                },
                {
                    name: "General Admin",
                    rows: [
                        { label: "C", seats: 12 },
                        { label: "D", seats: 12 }
                    ]
                }
            ]
        };

        $('#load-sample-btn').on('click', function() {
            $('#map-json').val(JSON.stringify(sampleJson, null, 2));
            renderPreview(sampleJson);
        });

        $('#save-map-btn').on('click', function() {
            const name = $('#map-name').val();
            let config;
            try {
                config = JSON.parse($('#map-json').val());
            } catch(e) {
                alert('Invalid JSON configuration.');
                return;
            }

            if (!name) {
                alert('Please enter a map name.');
                return;
            }

            $.ajax({
                url: restUrl + '/maps',
                method: 'POST',
                beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', nonce); },
                data: JSON.stringify({ name, config }),
                contentType: 'application/json',
                success: function(response) {
                    alert('Map saved successfully!');
                    renderPreview(config);
                    fetchExistingMaps();
                }
            });
        });

        function renderPreview(config) {
            const preview = $('#map-preview');
            preview.empty();
            if (!config.sections) return;

            const stage = $('<div>').text('STAGE / SCREEN').css({
                width: '80%', background: '#333', color: '#fff', 
                textAlign: 'center', padding: '10px', marginBottom: '40px', borderRadius: '4px'
            });
            preview.append(stage);

            config.sections.forEach(sec => {
                const secDiv = $('<div class="map-section">').append(`<strong>${sec.name}</strong>`);
                sec.rows.forEach(row => {
                    const rowDiv = $('<div class="map-row">').append(`<span style="width:20px">${row.label}</span>`);
                    for (let i=1; i<=row.seats; i++) {
                        rowDiv.append(`<div class="map-seat" title="Seat ${row.label}${i}">${i}</div>`);
                    }
                    secDiv.append(rowDiv);
                });
                preview.append(secDiv);
            });
        }

        function fetchExistingMaps() {
            $.ajax({
                url: restUrl + '/maps',
                beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', nonce); },
                success: function(response) {
                    const list = $('#existing-maps-list');
                    list.empty();
                    response.forEach(map => {
                        const li = $(`<li><strong>${map.name}</strong> (ID: ${map.id})</li>`);
                        li.css({ marginBottom: '10px', padding: '10px', background: '#f9f9f9', borderRadius: '4px' });
                        list.append(li);
                    });
                }
            });
        }

        fetchExistingMaps();
    });
    </script>
</div>
