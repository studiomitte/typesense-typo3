const typesenseInstantsearchAdapter = new TypesenseInstantSearchAdapter({
    server: {
        apiKey: document.currentScript.getAttribute('data-profile'),
        nodes: [
            {
                host: document.currentScript.getAttribute('data-host'),
                protocol: document.currentScript.getAttribute('data-protocol'),
            },
        ],
    },
    additionalSearchParameters: {
        query_by: '*',
         // pinned_hits: 'tx_youcard_domain_model_partner_1239:2'
    },
});
const searchClient = typesenseInstantsearchAdapter.searchClient;

const panelCssClasses = {
    header: 'text-primary text-xl',
    root: 'border border-gray-300 my-3 px-3 py-2'
}

const search = instantsearch({
    searchClient,
    routing: true,
    indexName: 'global',
});
const panelRefinementList = instantsearch.widgets.panel({
    templates: {
        header(options) {
            return TYPO3.lang['facet.' + options.widgetParams.attribute + '.header'] ?? options.widgetParams.attribute;
        }
    },
    cssClasses: panelCssClasses,
    hidden(options) {
        return options.items.length <= 1;
    },
})(
    instantsearch.widgets.refinementList
);
const facetSd = instantsearch.widgets.panel({
    templates: {
        header(options) {
            return TYPO3.lang['facet.' + options.widgetParams.attribute + '.header'] ?? options.widgetParams.attribute;
        }
    },
    cssClasses: panelCssClasses,
    hidden(options) {
        return options.items.length <= 1;
    },
})(
    instantsearch.widgets.refinementList
);
const facetCategory = instantsearch.widgets.panel({
    templates: {
        header(options) {
            return TYPO3.lang['facet.' + options.widgetParams.attribute + '.header'] ?? options.widgetParams.attribute;
        }
    },
    cssClasses: panelCssClasses
})(
    instantsearch.widgets.menu
);
const facetMenu = instantsearch.widgets.panel({
    templates: {
        header(options) {
            return TYPO3.lang['facet.' + options.widgetParams.attribute + '.header'] ?? options.widgetParams.attribute;
        }
    },
    cssClasses: panelCssClasses
})(
    instantsearch.widgets.hierarchicalMenu
);
const facetColor = instantsearch.widgets.panel({
    templates: {
        header(options) {
            return TYPO3.lang['facet.' + options.widgetParams.attribute + '.header'] ?? options.widgetParams.attribute;
        }
    },
    cssClasses: panelCssClasses,
    hidden(options) {
        return options.items.length <= 1;
    },
})(
    instantsearch.widgets.refinementList
);

const createDataAttribtues = refinement =>
    Object.keys(refinement)
        .map(key => `data-${key}="${refinement[key]}"`)
        .join(' ');

const renderListItem = item => `
  <li>
    ${item.attribute}
    <ul>
      ${item.refinements
    .map(refinement => `<li>` + ((TYPO3.lang['facet.' + item.attribute + '.item.' + refinement.value]) ?? `${refinement.value}`) + ` (${refinement.count})<button ${createDataAttribtues(refinement)}>X</button></li>`)
    .join('')}
    </ul>
  </li>
`;

const renderCurrentRefinements = (renderOptions, isFirstRender) => {
    const {items, refine, widgetParams} = renderOptions;

    const container = document.querySelector('#active-filters');
    console.log(items, 'items');
    container.innerHTML = `
    <ul>
      ${items.map(renderListItem).join('')}
    </ul>
  `;

    [...container.querySelectorAll('button')].forEach(element => {
        element.addEventListener('click', event => {
            const item = Object.keys(event.currentTarget.dataset).reduce(
                (acc, key) => ({
                    ...acc,
                    [key]: event.currentTarget.dataset[key],
                }),
                {}
            );

            refine(item);
        });
    });
};

const customCurrentRefinements = instantsearch.connectors.connectCurrentRefinements(
    renderCurrentRefinements
);

all = [
    container => panelRefinementList({
        container,
        attribute: 'table',
        searchable: false,
        showMore: false,
        limit: 50,
        showMoreLimit: 100,
        operator: 'or',
        transformItems(items) {
            return items.map(item => ({
                ...item,
                highlighted: TYPO3.lang['facet.table.item.' + item.highlighted] ?? item.highlighted,
            }));
        },
        cssClasses: {
            item: 'relative -mb-px flex justify-between py-1',
            searchableInput: 'form-control form-control-sm mb-2 border-light-2',
            searchableSubmit: 'd-none',
            searchableReset: 'd-none',
            showMore: 'btn btn-secondary btn-sm align-content-center',
            list: 'list-reset flex flex-col',
            count: 'inline-flex justify-center items-center rounded-full text-white h-full bg-primary px-2 pt-1 pb-px text-xs font-bold',
            label: 'd-flex align-items-center text-capitalize',
            checkbox: 'mr-2',
        }
    }),
    container => facetCategory({
        container,
        attribute: 'category_string_facet',
        searchable: false,
        showMore: false,
        limit: 50,
        showMoreLimit: 100,
        operator: 'or',
        // transformItems(items, {results}) {
        //                     return items.filter(item => item.label !== '');
        //                 },
        cssClasses: {
            item: 'relative -mb-px flex justify-between py-1',
            searchableInput: 'form-control form-control-sm mb-2 border-light-2',
            searchableSubmit: 'd-none',
            searchableReset: 'd-none',
            showMore: 'btn btn-secondary btn-sm align-content-center',
            list: 'list-reset flex flex-col',
            count: 'inline-flex justify-center items-center rounded-full text-white h-full bg-primary px-2 pt-1 pb-px text-xs font-bold',
            label: 'd-flex align-items-center text-capitalize',
            checkbox: 'mr-2',
        }
    }),
    container =>
        facetMenu({
            container,
            attributes: [
                'tree\.lvl0',
                'tree\.lvl1',
                'tree\.lvl2'
            ],
            searchable: false,
            showMore: false,
            limit: 50,
            showMoreLimit: 100,
            cssClasses: {
                item: 'relative -mb-px flex justify-between py-1',
                searchableInput: 'form-control form-control-sm mb-2 border-light-2',
                searchableSubmit: 'd-none',
                searchableReset: 'd-none',
                showMore: 'btn btn-secondary btn-sm align-content-center',
                list: 'list-reset flex flex-col',
                count: 'rounded-full text-white h-full bg-primary px-2 pt-1 pb-px text-xs font-bold',
                label: 'd-flex align-items-center text-capitalize',
                checkbox: 'mr-2',
            }
        }),
];

const fo = ['sd_string_facet', 'farbe_string_facet', 'uv_string_facet', 'city_string_facet'];
for (let i = 0; i < fo.length; i++) {
    let facetDynamic = instantsearch.widgets.panel({
        templates: {
            header(options) {
                return TYPO3.lang['facet.' + options.widgetParams.attribute + '.header'] ?? options.widgetParams.attribute;
            }
        },
        cssClasses: panelCssClasses,
        hidden(options) {
            return options.items.length <= 1;
        },
    })(
        instantsearch.widgets.refinementList
    );
    all.push(
        container => facetDynamic({
            container,
            attribute: fo[i],
            searchable: false,
            showMore: false,
            limit: 50,
            showMoreLimit: 100,
            // transformItems(items, {results}) {
            //     return items.filter(item => item.label !== '');
            // },
            cssClasses: {
                item: 'relative -mb-px flex justify-between py-1',
                searchableInput: 'form-control form-control-sm mb-2 border-light-2',
                searchableSubmit: 'd-none',
                searchableReset: 'd-none',
                showMore: 'btn btn-secondary btn-sm align-content-center',
                list: 'list-reset flex flex-col',
                count: 'inline-flex justify-center items-center rounded-full text-white h-full bg-primary px-2 pt-1 pb-px text-xs font-bold',
                label: 'd-flex align-items-center text-capitalize',
                selected: 'font-bold',
                checkbox: 'mr-2',
            }
        })
    );
}

let useGeosearch = document.currentScript.getAttribute('data-geosearch');
if (useGeosearch) {

    const placesElemeent = document.getElementById("searchBox-place");
// const mapElement = document.getElementById("map");
// var autocompletePlaces = new google.maps.places.Autocomplete();

    const createCustomPlacesWidget = ({defaultPosition = "0,0", ...options}) => {
        // const placesAutocomplete = places(options);
        var autocompletePlaces = null;
        const {attribute} = options;
        // var fo = '123';
        return {
            init: function (opts) {
                helper = opts.helper
                // console.log(opts.uiState, 'opts');

                autocompletePlaces = new google.maps.places.Autocomplete(
                    (document.getElementById('place')),
                    {
                        types: ['geocode']
                    });
                autocompletePlaces.setComponentRestrictions(
                    {'country': ['at']});

                autocompletePlaces.addListener('place_changed', function (eve) {
                    var place = autocompletePlaces.getPlace();
                    // console.log(place, 'place.geometry');
                    if (place.geometry) {
                        opts.helper.setQueryParameter('aroundLatLng', place.geometry.location.lat() + ',' + place.geometry.location.lng());
                        opts.helper.setQueryParameter('aroundRadius', 1000);
                    } else {
                        opts.helper.setQueryParameter('aroundLatLng', null);
                        opts.helper.setQueryParameter('aroundRadius', null);
                    }
                    opts.helper.search();
                });

            },

            getWidgetState(uiState, {searchParameters}) {
                const aroundLatLng = searchParameters.aroundLatLng;

                if (
                    aroundLatLng === "" ||
                    aroundLatLng === defaultPosition ||
                    (uiState && uiState.aroundLatLng === aroundLatLng)
                ) {
                    return uiState;
                }
                // console.log(autocompletePlaces.getPlace().formatted_address, 'autocompletePlaces');
                // console.log(fo, 'fo');
// console.log(autocompletePlaces, 'mow');
                return {
                    ...uiState,
                    placesLatLng: aroundLatLng,
                    placesInputValue: autocompletePlaces.getPlace().formatted_address
                };
            },

            getWidgetUiState(uiState, {searchParameters}) {
                // The global UI state is merged with a new one to store the UI
                // state of the current widget.
                // console
                // console.log(uiState, 'uistat');
                // console.log(searchParameters, 'sear');
                var newParam = '';
                if (autocompletePlaces.getPlace()) {
                    newParam = autocompletePlaces.getPlace().formatted_address;
                    // console.log(autocompletePlaces.getPlace().formatted_address, 'sear');
                }
                uiState.refinementList = uiState.refinementList || {};
                uiState.refinementList._geoloc = 'herexxx';

                // return uiState;
                //
                //
                // console.log(uiState, 'after');
                return {
                    ...uiState,
                    placesLatLng: {
                        ...uiState.placesLatLng,
                        // You can use multiple `negativeRefinementList` widgets in a single
                        // app so you need to register each of them separately.
                        // Each `negativeRefinementList` widget's UI state is stored by
                        // the `attribute` it impacts.
                        [attribute]: [newParam],
                    },
                };
            },

            getWidgetSearchParameters(searchParameters, {uiState}) {
                // console.log(fo, 'searchParameters');
                // console.log(uiState, 'uiState');
                // console.log('YEA');
                if (!uiState.placesLatLng || !uiState.placesInputValue) {
                    return searchParameters;
                }
                // placesAutocomplete.setVal(uiState.placesInputValue);
                // console.log(fo, 'searchParameters');
                return searchParameters;
                return searchParameters.setQueryParameter(
                    "aroundLatLng",
                    uiState.placesLatLng
                );
            }
        };
    };
    search.addWidgets([
        createCustomPlacesWidget({
            container: placesElemeent,
            attribute: '_geoloc',
        })
    ]);
    // all.push(
    // container =>
}

search.addWidgets([
    instantsearch.widgets.searchBox({
        container: '#searchbox',
    }),
    instantsearch.widgets.configure({
        hitsPerPage: 8,
    }),
    customCurrentRefinements({
        container: '#active-filters',
    }),

    instantsearch.widgets.clearRefinements({
        container: '#clear-filters',
        templates: {
            resetLabel({hasRefinements}) {
                if (!hasRefinements) {
                    return '';
                }
                return `<span>Clear refinements</span>`;
            },
        },
    }),
    instantsearch.widgets.dynamicWidgets({
        container: '#dynamic-widgets',
        widgets: all
    }),
    instantsearch.widgets.hits({
        container: '#hits',
        templates: {
            item(item) {
                let body = '';
                let curated = '';
                if (item._highlightResult.content && item._highlightResult.content.value) {
                    body = item._highlightResult.content.value;
                }
                if (item.curated) {
                    curated = ' [ADVERTISEMENT]';
                }
                return `
<div class="block border bg-white border-gray-20 hover:shadow-md transition duration-200 ease-in-out relative mb-4 p-3">
        <span class="font-black mb-3 md:custom-rm-after md:mb-0 md:text-xl text-2xl text-black">
            <a href="${item.url}" title="Downloads" class="custom-stretched-link text-primary hover:text-green-dark focus:underline">${item._highlightResult.title.value} [` + TYPO3.lang['facet.table.item.' + item.table] + ` ${curated}]</a>
        </span>
        <p class="result-content mt-2">${body}
        </p>
    </div>
                        <div>
                          <div class="hit-name">

                          </div>

                        </div>
                      `;
            },
        },
    }),
    instantsearch.widgets.pagination({
        container: '#pagination',
        scrollTo: false,
        cssClasses: {
            list: 'pagination',
            link: 'page-item',
            item: 'page-item',
            previousPageItem: 'page-item',
            selectedItem: 'page-item active',
        },
    }),
    instantsearch.widgets.hitsPerPage({
        container: '#hits-per-page',
        items: [
            {label: '8 hits per page', value: 8, default: true},
            {label: '12 hits per page', value: 12},
            {label: '14 hits per page', value: 14},
            {label: '16 hits per page', value: 16},
        ],
    }),
    instantsearch.widgets.stats({
        container: '#stats',
        templates: {
            text(data) {
                let count = '';

                if (data.hasManyResults) {
                    count += data.nbHits + ` results`;
                } else if (data.hasOneResult) {
                    count += `1 result`;
                } else {
                    return '';
                }
                return `<span class="result-found text-sm md:text-base md:font-bold">${count} found</span>`;
            },
        },
    }),
]);

function initMap() {
    search.start();
}

if (!useGeosearch) {
    search.start();
}
