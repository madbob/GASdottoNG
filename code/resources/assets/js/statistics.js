import { BarChart } from 'chartist';
import utils from "./utils";

class Statistics {
    static init(container)
    {
        setTimeout(() => {
            if ($('#stats-summary-form', container).length != 0) {
                this.runSummaryStats();

                $('#stats-summary-form').submit((event) => {
                    event.preventDefault();
                    this.runSummaryStats();
                });
            }

            if ($('#stats-supplier-form', container).length != 0) {
                this.runSupplierStats();

                $('#stats-supplier-form').submit((event) => {
                    event.preventDefault();
                    this.runSupplierStats();
                });
            }
        }, 500);
    }

    static doEmpty(target)
    {
        $(target).empty().css('height', 'auto').append($('#templates .alert').clone());
    }

    static commonGraphConfig()
    {
        return {
            horizontalBars: true,
            axisX: {
                onlyInteger: true
            },
            axisY: {
                offset: 220
            },
        };
    }

    static doGraph(selector, data)
    {
        if (data.labels.length == 0) {
            this.doEmpty(selector);
        }
        else {
            if ($(selector).length != 0) {
                $(selector).empty().css('height', data.labels.length * 40);
                const chart = new BarChart(selector, data, this.commonGraphConfig());

                chart.on('created', (data) => {
                    this.addDownloadButtons(data.svg['_node'], selector);
                });
            }
        }
    }

    static doGraphs(group, data)
    {
        this.doGraph('#stats-' + group + '-expenses', data.expenses);
        this.doGraph('#stats-' + group + '-users', data.users);
        this.doGraph('#stats-' + group + '-categories', data.categories);
    }

    static loadingGraphs(group)
    {
        $('#stats-' + group + '-expenses').empty().append(utils.j().makeSpinner());
        $('#stats-' + group + '-users').empty().append(utils.j().makeSpinner());
        $('#stats-' + group + '-categories').empty().append(utils.j().makeSpinner());
    }

    static runSummaryStats()
    {
        this.loadingGraphs('generic');

        $.getJSON(utils.absoluteUrl() + '/stats/summary', {
            startdate: $('#stats-summary-form input[name=startdate]').val(),
            enddate: $('#stats-summary-form input[name=enddate]').val(),
            target: $('#stats-summary-form input[name=target]').val(),
            type: $('#stats-summary-form select[name=type]').val(),
            format: 'json',
        }, (data) => {
            this.doGraphs('generic', data);
        });
    }

    static runSupplierStats()
    {
        this.loadingGraphs('products');

        $.getJSON(utils.absoluteUrl() + '/stats/supplier', {
            supplier: $('#stats-supplier-form select[name=supplier] option:selected').val(),
            startdate: $('#stats-supplier-form input[name=startdate]').val(),
            enddate: $('#stats-supplier-form input[name=enddate]').val(),
            target: $('#stats-supplier-form input[name=target]').val(),
            type: $('#stats-summary-form select[name=type]').val(),
            format: 'json',
        }, (data) => {
            this.doGraphs('products', data);
        });
    }

    /* static addBootstrapDropdown(selector) 
    {
        const container = document.createElement('div');
        container.className = 'dropdown';

        const btn = document.createElement('button');
        btn.classList.add('btn', 'btn-light', 'form-download');
        btn.type = 'button';
        btn.innerText = 'Esporta ';
        btn.setAttribute('data-bs-toggle', 'dropdown');
        btn.setAttribute('aria-expanded', 'false');
        container.appendChild(btn);

        const i = document.createElement('i');
        i.className = 'bi-download';
        btn.appendChild(i);

        const ul = document.createElement('ul');
        ul.className = 'dropdown-menu';
        container.appendChild(ul);

        $(selector).prev().append(container);   
        
        return ul;
    } */

    static async addDownloadButtons(svg, selector) 
    {
        const url = await svgToImage(svg);
        
        /* for (const type in url) {
            const li = document.createElement('li');
            root.appendChild(li);

            // const firstLetter = type.charAt(0);
            const a = document.createElement('a');
            a.classList.add('dropdown-item', 'form-download');
            a.href = url[type];
            a.download = filename.replace('#', '') + '.' + type;
            a.text = type.replace(firstLetter, firstLetter.toUpperCase());
            li.appendChild(a);
        } */

        const a = document.createElement('a');
        a.classList.add('btn', 'btn-light', 'form-download');
        a.href = url;
        a.download = selector.replace('#', '') + '.jpeg';
        a.text = 'Esporta ';

        const i = document.createElement('i');
        i.className = 'bi-download';
        a.append(i);

        $(selector).prev().append(a);
    }
}

function svgToImage(svgElement) 
{
    return new Promise((resolve, reject) => {
        // 1. Clone the SVG to avoid messing with the live page
        const clonedSvg = svgElement.cloneNode(true);

        // 2. Load element classes to inline style 
        inlineStyle(svgElement, clonedSvg);

        // 3. Validate SVG
        validateSVG(clonedSvg);

        // 4. Serialize SVG and create URL
        const svgData = new XMLSerializer().serializeToString(clonedSvg);
        const base64Svg = toBase64(svgData);
        const url = 'data:image/svg+xml;charset=utf-8;base64,' + base64Svg;

        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.src = url;

        img.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width = svgElement.clientWidth;
            canvas.height = svgElement.clientHeight;

            const ctx = canvas.getContext('2d');
            ctx.fillStyle = "white";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0);
            
            // const jpegUrl = canvas.toDataURL('image/jpeg');
            // const pngUrl = canvas.toDataURL('image/png');

            const dataUrl = canvas.toDataURL('image/jpeg');

            URL.revokeObjectURL(url);
            resolve(dataUrl /* {
                'jpeg': jpegUrl, 
                'png': pngUrl
            } */);
        }

        img.onerror = (error) => {
            URL.revokeObjectURL(url);
            reject(error);
        }
    });
}

function toBase64(data) 
{
    // TextEncoder: Always UTF8
    const uInt8Array = new TextEncoder().encode(data);
    let binary = '';

    for (let i = 0; i < uInt8Array.length; ++i)
        binary += String.fromCharCode(uInt8Array[i]);

    return btoa(binary);
}

function inlineStyle(original, clone) 
{
    // 1. Get all elements from both the original and the clone
    const originalElements = original.querySelectorAll('*');
    const clonedElements = clone.querySelectorAll('*');

    // 2. Loop through and "bake" the computed styles
    originalElements.forEach((el, i) => {
        const computed = getComputedStyle(el);
        const target = clonedElements[i];

        // List of critical SVG styles to preserve
        const styleProps = [
            'fill', 'stroke', 'stroke-width', 'opacity', 
            'display', 'font-family', 'font-size', 'stop-color'
        ];

        styleProps.forEach(prop => {
            target.style[prop] = computed.getPropertyValue(prop);
        });
    });
}

function validateSVG(svg) 
{
    const elements = svg.querySelectorAll('*');
    elements.forEach(el => {
        if(el instanceof HTMLElement) {
            el.removeAttribute('xmlns');
        }
    })
} 

export default Statistics;
