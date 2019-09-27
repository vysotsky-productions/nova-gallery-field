const findIndex = searchCallback => arr => {
    const idx = arr.findIndex(searchCallback);

    console.log(idx);
    return idx === -1 ? false : idx;
};

export {findIndex};
