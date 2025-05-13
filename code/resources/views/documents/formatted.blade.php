<html>
    <head>
        <style>
            table {
                border-spacing: 0;
                border-collapse: collapse;
            }

            .main-wrapper {
                display: table;
                width: 100%;
            }

            .row {
                display: table-row;
                width: 100%;
            }

            .cell {
                border-top: 1px solid #000;
                border-left: 1px solid #000;
                display: table-cell;
                padding: 4px;
            }

            .cell.last-row {
                border-bottom: 1px solid #000;
            }

            .cell.last-in-row {
                border-right: 1px solid #000;
            }

            .extended {
                font-weight: bold;
                text-align: center;
                display: block;
                padding: 4px 0;
                width: 100%;
            }

            ul {
                margin: 0;
            }

            li {
                margin: 0;
            }

            h3, h4 {
                width: 100%;
                background-color: #555;
                color: #FFF;
                text-align: center;
                padding: 5px;
            }
        </style>
    </head>

    <body>
        {!! $document->renderHtml() !!}
    </body>
</html>
